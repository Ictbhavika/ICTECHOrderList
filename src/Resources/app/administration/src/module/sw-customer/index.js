import './page/sw-customer-detail';
import './view/sw-customer-detail-ict-order-list';

const { Module } = Shopware;

Module.register('ict-order-list', {
    routeMiddleware(next, currentRoute) {
        if (currentRoute.name === 'sw.customer.detail') {
            currentRoute.children.push({
                name: 'sw.customer.detail.ict.order.list',
                path: 'ict-order-list',
                component: 'sw-customer-detail-ict-order-list',
                meta: {
                    parentPath: 'sw.customer.index',
                },
            });
        }
        next(currentRoute);
    },
});
