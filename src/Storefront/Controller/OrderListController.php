<?php declare(strict_types=1);

namespace ICTECHOrderList\Storefront\Controller;

use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class OrderListController  extends StorefrontController 
{
    #[Route(path: '/account/order-list', name: 'frontend.account.order-list.page', options: ['seo' => false], defaults: ['_loginRequired' => true, '_noStore' => true], methods: ['GET', 'POST'])]
    public function index(Request $request, SalesChannelContext $context): Response
    {
        //dd("1");
       // $page = $this->fastOrderPageLoader->load($request, $context);
        $page = null;
        return $this->renderStorefront('@Storefront/storefront/page/account/order-list/index.html.twig', ['page' => $page]);
    }

    #[Route(path: '/order-list/create', name: 'frontend.order-list.create', methods: ['POST'])]
    public function create(Request $request, SalesChannelContext $context): RedirectResponse
    {
        $name = $request->request->get('name');

        // create via repository
        // $this->orderListRepository->create([ 
        //     [
        //         'name' => $name,
        //         'customerId' => $context->getCustomerId()
        //     ]
        // ], $context);

        return $this->redirectToRoute('frontend.order-list.index');
    }
}