import template from './sw-customer-detail.html.twig';

const { Component } = Shopware;

Component.override('sw-customer-detail', {
    template,

    computed: {
        ictOrderListRoute() {
            return {
                name: 'sw.customer.detail.ict.order.list',
                params: { id: this.customerId }
            };
        }
    }
});
