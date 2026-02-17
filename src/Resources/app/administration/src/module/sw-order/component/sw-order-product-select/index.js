const { Component} = Shopware;
const { Criteria }   = Shopware.Data;

Component.override('sw-order-product-select', {
    computed: {
        productCriteria() {
            const criteria = this.$super('productCriteria');

            criteria.addAssociation('netiEasyCouponProduct');

            criteria.addFilter(Criteria.equals('netiEasyCouponProduct.id', null));

            return criteria;
        },
    },
});