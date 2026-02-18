<?php

declare(strict_types=1);

namespace ICTECHOrderList\Storefront\Controller;

use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\Routing\Attribute\Route;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class OrderListController  extends StorefrontController
{
    public function __construct(
        private readonly SalesChannelRepository $productRepository,
        private readonly EntityRepository $orderListRepository,
        private readonly EntityRepository $orderProductListRepository
    ) {
    }

    #[Route(path: '/account/order-list', name: 'frontend.account.order-list.page', options: ['seo' => false], defaults: ['_loginRequired' => true, '_noStore' => true], methods: ['GET', 'POST'])]
    public function index(Request $request, SalesChannelContext $context): Response
    {
        $customerId = $context->getCustomerId();
        $page = max(1, (int) $request->query->get('p', 1));
        $limit = 10;

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('customerId', [$customerId]));
        $criteria->addAssociation('orderListProduct');
        $criteria->setLimit($limit);
        $criteria->setOffset(($page - 1) * $limit);
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);

        $orderLists = $this->orderListRepository->search($criteria, $context->getContext());

        // Add product count to each order list
        foreach ($orderLists as $orderList) {
            $orderList->productCount = $orderList->getExtension('orderListProduct') ? count($orderList->getExtension('orderListProduct')) : 0;
        }

        return $this->renderStorefront('@Storefront/storefront/page/account/order-list/index.html.twig', [
            'orderLists' => $orderLists
        ]);
    }

    #[Route(
        path: '/account/order-list/csv-demo',
        name: 'frontend.account.order-list.csv.demo',
                defaults: ['_loginRequired' => true, '_noStore' => true],
                methods: ['GET']
    )]
    public function downloadDemo(SalesChannelContext $context): Response
    {
        $csv = "productNumber,quantity\nSW10001,5\nSW10002,3";
        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="order-list-demo.csv"');
        return $response;
    }
    

    #[Route(path: '/order-list/create', name: 'frontend.order-list.create', defaults: ['_loginRequired' => true, '_noStore' => true], methods: ['POST'])]
    public function create(Request $request, SalesChannelContext $context): Response
    {
        $orderListName = trim((string) $request->request->get('orderListName', ''));
        $csvFile = $request->files->get('csvFile');
        
        if (empty($orderListName)) {
            $this->addFlash(self::DANGER, 'Order list name is required');
            return $this->redirectToRoute('frontend.account.order-list.page');
        }
        
        if (strlen($orderListName) < 3) {
            $this->addFlash(self::DANGER, 'Order list name must be at least 3 characters');
            return $this->redirectToRoute('frontend.account.order-list.page');
        }
        
        if (strlen($orderListName) > 100) {
            $this->addFlash(self::DANGER, 'Order list name must not exceed 100 characters');
            return $this->redirectToRoute('frontend.account.order-list.page');
        }
        
        if (!preg_match('/^[a-zA-Z0-9\s\-_.,&äöüßÄÖÜ]+$/', $orderListName)) {
            $this->addFlash(self::DANGER, 'Order list name contains invalid characters');
            return $this->redirectToRoute('frontend.account.order-list.page');
        }
        
        // Parse CSV if provided
        $products = [];
        
        if ($csvFile && $csvFile->getSize() > 0) {
            // Validate CSV file
            $allowed = ['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/csv'];
            $mimeType = (string) $csvFile->getMimeType();
            $extension = strtolower((string) $csvFile->getClientOriginalExtension());
            
            if (!in_array($mimeType, $allowed, true) || $extension !== 'csv') {
                $this->addFlash(self::DANGER, 'Only CSV files are allowed');
                return $this->redirectToRoute('frontend.account.order-list.page');
            }
            
            if ($csvFile->getSize() > 5 * 1024 * 1024) {
                $this->addFlash(self::DANGER, 'CSV file must be smaller than 5MB');
                return $this->redirectToRoute('frontend.account.order-list.page');
            }
            
            // Parse CSV content
            $rawContent = (string) file_get_contents($csvFile->getPathname());
            if (empty($rawContent)) {
                $this->addFlash(self::DANGER, 'CSV file is empty');
                return $this->redirectToRoute('frontend.account.order-list.page');
            }
            
            $lines = array_filter(explode("\n", $rawContent));
            $header = null;
            
            foreach ($lines as $lineNum => $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                $fields = str_getcsv($line);
                
                if ($header === null) {
                    $header = array_map('trim', $fields);
                    continue;
                }
                
                if (count($fields) < count($header)) continue;
                
                $row = array_combine($header, array_slice($fields, 0, count($header)));
                $productNumber = trim((string) ($row['productNumber'] ?? $row['Product Number'] ?? ''));
                $quantity = (int) ($row['quantity'] ?? $row['Quantity'] ?? 1);
                
                if ($productNumber !== '' && $quantity > 0) {
                    $products[] = ['productNumber' => $productNumber, 'quantity' => $quantity];
                }
            }
        }
        
        // Create order list
        $orderListId = Uuid::randomHex();
        
        try {
            $this->orderListRepository->create([[
                'id' => $orderListId,
                'name' => $orderListName,
                'customerId' => $context->getCustomerId(),
            ]], $context->getContext());
            
            // Add products if any
            if (count($products) > 0) {
                $productIds = array_column($products, 'productNumber');
                $criteria = new Criteria();
                $criteria->addFilter(new EqualsAnyFilter('productNumber', $productIds));
                $foundProducts = $this->productRepository->search($criteria, $context);
                
                $orderProducts = [];
                foreach ($products as $item) {
                    $product = null;
                    foreach ($foundProducts as $p) {
                        if ($p->getProductNumber() === $item['productNumber']) {
                            $product = $p;
                            break;
                        }
                    }
                    
                    if (!$product) continue;
                    
                    $orderProducts[] = [
                        'id' => Uuid::randomHex(),
                        'orderListId' => $orderListId,
                        'productId' => $product->getId(),
                        'productNumber' => $item['productNumber'],
                        'qty' => $item['quantity'],
                    ];
                }
                
                if (count($orderProducts) > 0) {
                    $this->orderProductListRepository->create($orderProducts, $context->getContext());
                }
                
                $linkedCount = count($orderProducts);
                $notFoundCount = count($products) - $linkedCount;
                
                if ($notFoundCount > 0) {
                    $this->addFlash(self::WARNING, "Order list created. {$linkedCount} of " . count($products) . " products added. {$notFoundCount} products not found.");
                } else {
                    $this->addFlash(self::SUCCESS, "Order list '{$orderListName}' created successfully with {$linkedCount} products.");
                }
            } else {
                $this->addFlash(self::SUCCESS, "Order list '{$orderListName}' created successfully.");
            }
            
            return $this->redirectToRoute('frontend.order-list.detail', ['id' => $orderListId]);
            
        } catch (\Exception $e) {
            $this->addFlash(self::DANGER, 'Error creating order list: ' . $e->getMessage());
            return $this->redirectToRoute('frontend.account.order-list.page');
        }
    }

    #[Route(path: '/order-list/{id}/upload', name: 'frontend.order-list.upload', defaults: ['_loginRequired' => true, 'XmlHttpRequest' => true, '_noStore' => true], methods: ['POST'])]
    public function upload(string $id, Request $request, SalesChannelContext $context): Response
    {
        $csvFile = $request->files->get('csvFile');
        
        if (!$csvFile) {
            $this->addFlash(self::DANGER, 'No file uploaded');
            return $this->redirectToRoute('frontend.order-list.detail', ['id' => $id]);
        }

        $allowed = ['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/csv'];
        if (!in_array((string) $csvFile->getMimeType(), $allowed, true) || strtolower((string) $csvFile->getClientOriginalExtension()) !== 'csv') {
            $this->addFlash(self::DANGER, 'Only CSV files allowed');
            return $this->redirectToRoute('frontend.order-list.detail', ['id' => $id]);
        }

        $raw = (string) file_get_contents($csvFile->getPathname());
        if ($raw === '') {
            $this->addFlash(self::DANGER, 'CSV file is empty');
            return $this->redirectToRoute('frontend.order-list.detail', ['id' => $id]);
        }

        $lines = array_filter(explode("\n", $raw));
        $header = null;
        $products = [];

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $fields = str_getcsv($line);
            
            if ($header === null) {
                $header = array_map('trim', $fields);
                continue;
            }
            
            if (count($fields) < count($header)) continue;
            
            $row = array_combine($header, array_slice($fields, 0, count($header)));
            $productNumber = trim((string) ($row['productNumber'] ?? $row['Product Number'] ?? ''));
            $quantity = (int) ($row['quantity'] ?? $row['Quantity'] ?? 1);
            
            if ($productNumber !== '' && $quantity > 0) {
                $products[] = ['productNumber' => $productNumber, 'quantity' => $quantity];
            }
        }

        if (empty($products)) {
            $this->addFlash(self::DANGER, 'No valid products found');
            return $this->redirectToRoute('frontend.order-list.detail', ['id' => $id]);
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('productNumber', array_column($products, 'productNumber')));
        $foundProducts = $this->productRepository->search($criteria, $context);

        $criteria = new Criteria([$id]);
        $criteria->addAssociation('orderListProduct');
        $orderList = $this->orderListRepository->search($criteria, $context->getContext())->first();

        $existingProductIds = [];
        if ($orderList && $orderList->getExtension('orderListProduct')) {
            foreach ($orderList->getExtension('orderListProduct') as $item) {
                $existingProductIds[$item->get('productId')] = true;
            }
        }

        $orderProducts = [];
        $added = 0;
        $skipped = 0;

        foreach ($products as $item) {
            $product = null;
            foreach ($foundProducts as $p) {
                if ($p->getProductNumber() === $item['productNumber']) {
                    $product = $p;
                    break;
                }
            }
            
            if (!$product || isset($existingProductIds[$product->getId()])) {
                $skipped++;
                continue;
            }
            
            $orderProducts[] = [
                'id' => Uuid::randomHex(),
                'orderListId' => $id,
                'productId' => $product->getId(),
                'productNumber' => $item['productNumber'],
                'qty' => $item['quantity'],
            ];
            $added++;
        }

        if (!empty($orderProducts)) {
            $this->orderProductListRepository->create($orderProducts, $context->getContext());
        }

        $this->addFlash(self::SUCCESS, "Added {$added} products" . ($skipped > 0 ? ", skipped {$skipped} duplicates" : ''));
        return $this->redirectToRoute('frontend.order-list.detail', ['id' => $id]);
    }

    #[Route(path: '/order-list/product/update-qty', name: 'frontend.order-list.product.update-qty', defaults: ['_loginRequired' => true, 'XmlHttpRequest' => true, '_noStore' => true], methods: ['POST'])]
    public function updateQuantity(Request $request, SalesChannelContext $context): Response
    {
        $data = json_decode($request->getContent(), true);
        $itemId = $data['itemId'] ?? null;
        $qty = max(1, (int) ($data['qty'] ?? 1));

        if (!$itemId) {
            return $this->json(['success' => false, 'error' => 'Invalid item ID'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->orderProductListRepository->update([[
                'id' => $itemId,
                'qty' => $qty
            ]], $context->getContext());

            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route(path: '/order-list/product/{itemId}/delete', name: 'frontend.order-list.product.delete', defaults: ['_loginRequired' => true, '_noStore' => true], methods: ['POST', 'DELETE'])]
    public function deleteProduct(string $itemId, Request $request, SalesChannelContext $context): Response
    {
        $criteria = new Criteria([$itemId]);
        $orderProduct = $this->orderProductListRepository->search($criteria, $context->getContext())->first();
        $orderListId = $orderProduct ? $orderProduct->get('orderListId') : null;

        try {
            $this->orderProductListRepository->delete([['id' => $itemId]], $context->getContext());
            $this->addFlash(self::SUCCESS, 'Product removed from list');
        } catch (\Exception $e) {
            $this->addFlash(self::DANGER, 'Failed to remove product');
        }
        
        if ($orderListId) {
            return $this->redirectToRoute('frontend.order-list.detail', ['id' => $orderListId]);
        }
        
        return $this->redirectToRoute('frontend.account.order-list.page');
    }

    #[Route(path: '/order-list/{id}/products', name: 'frontend.order-list.products', defaults: ['_loginRequired' => true, 'XmlHttpRequest' => true, '_noStore' => true], methods: ['GET'])]
    public function getProducts(string $id, SalesChannelContext $context): Response
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('orderListProduct.product');
        $criteria->addAssociation('orderListProduct.product.cover.media');
        $orderList = $this->orderListRepository->search($criteria, $context->getContext())->first();

        if (!$orderList) {
            return $this->json(['success' => false, 'error' => 'Order list not found'], Response::HTTP_NOT_FOUND);
        }

        $products = [];
        if ($orderList->getExtension('orderListProduct')) {
            foreach ($orderList->getExtension('orderListProduct') as $item) {
                $product = $item->get('product');
                $products[] = [
                    'id' => $item->get('id'),
                    'productNumber' => $item->get('productNumber'),
                    'name' => $product ? $product->getTranslated()['name'] : null,
                    'quantity' => $item->get('qty'),
                    'image' => $product && $product->getCover() ? $product->getCover()->getMedia()->getUrl() : null,
                    'price' => null,
                    'total' => null
                ];
            }
        }

        return $this->json(['success' => true, 'products' => $products]);
    }

    #[Route(path: '/order-list/{id}', name: 'frontend.order-list.detail', defaults: ['_loginRequired' => true, '_noStore' => true], methods: ['GET'])]
    public function detail(string $id, Request $request, SalesChannelContext $context): Response
    {
        $session = $request->getSession();
        $session->set('order_list_id_to_delete', $id);
        
        $page = max(1, (int) $request->query->get('p', 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $criteria = new Criteria([$id]);
        $criteria->addAssociation('orderListProduct');
        $orderList = $this->orderListRepository->search($criteria, $context->getContext())->first();

        if (!$orderList) {
            throw $this->createNotFoundException();
        }

        $allProducts = $orderList->getExtension('orderListProduct');
        $total = $allProducts ? count($allProducts) : 0;
        $paginatedProducts = $allProducts ? array_slice($allProducts->getElements(), $offset, $limit) : [];

        // Load products with prices through SalesChannelRepository
        if (!empty($paginatedProducts)) {
            $productIds = array_map(fn($item) => $item->get('productId'), $paginatedProducts);
            $productCriteria = new Criteria($productIds);
            $productCriteria->addAssociation('cover.media');
            $loadedProducts = $this->productRepository->search($productCriteria, $context);

            foreach ($paginatedProducts as $item) {
                $product = $loadedProducts->get($item->get('productId'));
                if ($product) {
                    $item->product = $product;
                }
            }
        }

        $collection = new EntityCollection($paginatedProducts);
        $criteria = new Criteria();
        $criteria->setLimit($limit);
        $criteria->setOffset($offset);
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);
        
        $productSearchResult = new EntitySearchResult(
            'order_list_product',
            $total,
            $collection,
            null,
            $criteria,
            $context->getContext()
        );

        $orderList->addExtension('orderListProduct', new ArrayStruct($paginatedProducts));
        
        return $this->renderStorefront('@Storefront/storefront/page/account/order-list/detail.html.twig', [
            'orderList' => $orderList,
            'products' => $productSearchResult
        ]);
    }

    #[Route(path: '/order-list/rename', name: 'frontend.order-list.rename', defaults: ['_loginRequired' => true, '_noStore' => true], methods: ['POST'])]
    public function rename(Request $request, SalesChannelContext $context): RedirectResponse
    {
        $listId = $request->request->get('listId');
        $name = trim((string) $request->request->get('name'));

        if (empty($name)) {
            $this->addFlash(self::DANGER, 'Name is required');
            return $this->redirectToRoute('frontend.account.order-list.page');
        }

        try {
            $this->orderListRepository->update([['id' => $listId, 'name' => $name]], $context->getContext());
            $this->addFlash(self::SUCCESS, 'List renamed successfully');
        } catch (\Exception $e) {
            $this->addFlash(self::DANGER, 'Failed to rename list');
        }

        return $this->redirectToRoute('frontend.account.order-list.page');
    }

    #[Route(path: '/order-list/duplicate', name: 'frontend.order-list.duplicate', defaults: ['_loginRequired' => true, '_noStore' => true], methods: ['POST'])]
    public function duplicate(Request $request, SalesChannelContext $context): RedirectResponse
    {
        $listId = $request->request->get('listId');
        $name = trim((string) $request->request->get('name'));

        if (empty($name)) {
            $this->addFlash(self::DANGER, 'Name is required');
            return $this->redirectToRoute('frontend.account.order-list.page');
        }

        try {
            $criteria = new Criteria([$listId]);
            $criteria->addAssociation('orderListProduct');
            $orderList = $this->orderListRepository->search($criteria, $context->getContext())->first();

            if (!$orderList) {
                $this->addFlash(self::DANGER, 'List not found');
                return $this->redirectToRoute('frontend.account.order-list.page');
            }

            $newListId = Uuid::randomHex();
            $this->orderListRepository->create([[
                'id' => $newListId,
                'name' => $name,
                'customerId' => $context->getCustomerId(),
            ]], $context->getContext());

            if ($orderList->getExtension('orderListProduct')) {
                $products = [];
                foreach ($orderList->getExtension('orderListProduct') as $item) {
                    $products[] = [
                        'id' => Uuid::randomHex(),
                        'orderListId' => $newListId,
                        'productId' => $item->get('productId'),
                        'productNumber' => $item->get('productNumber'),
                        'qty' => $item->get('qty'),
                    ];
                }
                if (!empty($products)) {
                    $this->orderProductListRepository->create($products, $context->getContext());
                }
            }

            $this->addFlash(self::SUCCESS, 'List duplicated successfully');
        } catch (\Exception $e) {
            $this->addFlash(self::DANGER, 'Failed to duplicate list');
        }

        return $this->redirectToRoute('frontend.account.order-list.page');
    }

    #[Route(path: '/order-list/{id}/add-all-to-cart', name: 'frontend.order-list.add-all-to-cart', defaults: ['_loginRequired' => true, 'XmlHttpRequest' => true, '_noStore' => true], methods: ['POST'])]
    public function addAllToCart(string $id, SalesChannelContext $context): Response
    {

    
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('orderListProduct');
        $orderList = $this->orderListRepository->search($criteria, $context->getContext())->first();
        if (!$orderList || !$orderList->getExtension('orderListProduct')) {
            return $this->json(['success' => false, 'error' => 'No products found'], Response::HTTP_NOT_FOUND);
        }

        $items = [];
        foreach ($orderList->getExtension('orderListProduct') as $item) {
            $items[] = [
                'id' => $item->get('productId'),
                'quantity' => $item->get('qty'),
                'type' => 'product',
                'referencedId' => $item->get('productId')
            ];
        }
        dd($items);

        return $this->json(['success' => true, 'items' => $items]);
    }

    #[Route(path: '/order-list/delete', name: 'frontend.order-list.delete', defaults: ['_loginRequired' => true, '_noStore' => true], methods: ['POST'])]
    public function delete(Request $request, SalesChannelContext $context): RedirectResponse
    {
        $listId = $request->request->get('listId');

        try {
            $this->orderListRepository->delete([['id' => $listId]], $context->getContext());
            $this->addFlash(self::SUCCESS, 'List deleted successfully');
        } catch (\Exception $e) {
            $this->addFlash(self::DANGER, 'Failed to delete list');
        }

        return $this->redirectToRoute('frontend.account.order-list.page');
    }
}
