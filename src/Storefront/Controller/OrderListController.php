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

#[Route(defaults: ['_routeScope' => ['storefront']])]
class OrderListController  extends StorefrontController
{
    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly EntityRepository $orderListRepository,
        private readonly EntityRepository $orderProductListRepository
    ) {
    }

    #[Route(path: '/account/order-list', name: 'frontend.account.order-list.page', options: ['seo' => false], defaults: ['_loginRequired' => true, '_noStore' => true], methods: ['GET', 'POST'])]
    public function index(Request $request, SalesChannelContext $context): Response
    {
        $customerId = $context->getCustomerId();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('customerId', [$customerId]));
        $criteria->addAssociation('orderListProduct');
        $criteria->setLimit(12);
        $criteria->setOffset((int) $request->query->get('p', 1) - 1);

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
        dd("``");
        // $customerId = $context->getCustomerId();
        // $selection  = $this->fastOrderRoute->load($customerId, $context)->getFastOrderCollection();
        // $csv        = $this->buildCsvFromSelection($selection, $context->getContext());

        // $response = new Response($csv);
        // $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        // $response->headers->set('Content-Disposition', 'attachment; filename="fast-order.csv"');

        // return $response;
    }

    #[Route(path: '/order-list/create', name: 'frontend.order-list.create', defaults: ['_loginRequired' => true, 'XmlHttpRequest' => true, '_noStore' => true], methods: ['POST'])]
    public function create(Request $request, SalesChannelContext $context): Response
    {
     //   dd($request, $request->request->all());
        // Get order list name from request
        $orderListName = trim((string) $request->request->get('orderListName', ''));
        //dd($orderListName);
        // Get CSV file if provided
        $csvFile = $request->files->get('csvFile');
        
        // Validate order list name
        if (empty($orderListName)) {
            return $this->json([
                'success' => false,
                'error' => 'Order list name is required'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        if (strlen($orderListName) > 255) {
            return $this->json([
                'success' => false,
                'error' => 'Order list name must be 255 characters or less'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Parse CSV if provided
        $products = [];
        $csvErrors = [];
        
        if ($csvFile && $csvFile->getSize() > 0) {
            // Validate CSV file
            $allowed = ['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/csv'];
            $mimeType = (string) $csvFile->getMimeType();
            $extension = strtolower((string) $csvFile->getClientOriginalExtension());
            
            if (!in_array($mimeType, $allowed, true) || $extension !== 'csv') {
                return $this->json([
                    'success' => false,
                    'error' => 'Only CSV files are allowed'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            if ($csvFile->getSize() > 5 * 1024 * 1024) { // 5MB limit
                return $this->json([
                    'success' => false,
                    'error' => 'CSV file must be smaller than 5MB'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Parse CSV content
            $rawContent = (string) file_get_contents($csvFile->getPathname());
            if (empty($rawContent)) {
                return $this->json([
                    'success' => false,
                    'error' => 'CSV file is empty'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $lines = array_filter(explode("\n", $rawContent));
            $header = null;
            
            foreach ($lines as $lineNum => $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                
                $fields = str_getcsv($line);
                
                // Parse header
                if ($header === null) {
                    $header = array_map('trim', $fields);
                    continue;
                }
                
                // Parse data rows
                if (count($fields) < count($header)) {
                    $csvErrors[] = "Line " . ($lineNum + 1) . ": Missing columns";
                    continue;
                }
                
                $row = array_combine($header, array_slice($fields, 0, count($header)));
                
                $productNumber = trim((string) ($row['productNumber'] ?? $row['Product Number'] ?? ''));
                $quantity = (int) ($row['quantity'] ?? $row['Quantity'] ?? 1);
                
                if (empty($productNumber)) {
                    $csvErrors[] = "Line " . ($lineNum + 1) . ": Product number is empty";
                    continue;
                }
                
                if ($quantity <= 0) {
                    $csvErrors[] = "Line " . ($lineNum + 1) . ": Quantity must be greater than 0";
                    continue;
                }
                
                $products[] = [
                    'productNumber' => $productNumber,
                    'quantity' => $quantity,
                ];
            }
            
            // Show CSV parsing errors but continue (warnings)
            $warnings = [];
            foreach (array_slice($csvErrors, 0, 10) as $error) {
                $warnings[] = 'CSV Error: ' . $error;
            }
            if (count($csvErrors) > 10) {
                $warnings[] = 'And ' . (count($csvErrors) - 10) . ' more CSV errors';
            }
        }
        
        // Create order list record in database
        $orderListId = Uuid::randomHex();
        
        try {
            // Create the order list
            $this->orderListRepository->create([
                [
                    'id' => $orderListId,
                    'name' => $orderListName,
                    'customerId' => $context->getCustomerId(),
                ]
            ], $context->getContext());
            
            // Add validated products to order list
            if (count($products) > 0) {
                $productIds = array_column($products, 'productNumber');
                
                // Fetch products from database
                $criteria = new Criteria();
                $criteria->addFilter(new EqualsAnyFilter('productNumber', $productIds));
                $foundProducts = $this->productRepository->search($criteria, $context->getContext());
                
                // Create order product list items
                
                $orderProducts = [];
                foreach ($products as $item) {
                    // Find product by product number
                    $product = null;
                    foreach ($foundProducts as $p) {
                        if ($p->getProductNumber() === $item['productNumber']) {
                            $product = $p;
                            break;
                        }
                    }
                    
                    if (!$product) {
                        continue;
                    }
                    
                    $orderProducts[] = [
                        'id' => Uuid::randomHex(),
                        'orderListId' => $orderListId,
                        'productId' => $product->getId(),
                        'productNumber' => $item['productNumber'],
                        'qty' => $item['quantity'],
                    ];
                }
                
                // Persist order products
                if (count($orderProducts) > 0) {
                    $this->orderProductListRepository->create($orderProducts, $context->getContext());
                }
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error creating order list: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        $productCount = count($products);
        $message = "Order list '{$orderListName}' created";
        if ($productCount > 0) {
            $message .= " with {$productCount} product(s)";
        }
        
        return $this->json([
            'success' => true,
            'message' => $message,
            'orderListId' => $orderListId,
            'productCount' => $productCount
        ]);
    }

     #[Route(path: '/order-list/manage', name: 'frontend.order-list.cart.manage', defaults: ['_loginRequired' => true, '_noStore' => true], methods: ['POST'])]
    public function manage(Request $request, SalesChannelContext $context): RedirectResponse
    {
        dd("manage");
      
    }
}
