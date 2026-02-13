<?php declare(strict_types=1);

namespace ICTECHOrderList\Storefront\Controller;

use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class OrderListController  extends StorefrontController 
{
    #[Route(path: '/account/order-list', name: 'frontend.account.order-list.page', options: ['seo' => false], defaults: ['_loginRequired' => true, '_noStore' => true], methods: ['GET', 'POST'])]
    public function index(Request $request, SalesChannelContext $context): Response
    {
        dd("1");
       // $page = $this->fastOrderPageLoader->load($request, $context);

       // return $this->renderStorefront('@Storefront/storefront/page/account/acris-fast-order/index.html.twig', ['page' => $page]);
    }
}