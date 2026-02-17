Shopware.Component.override('sw-order-list', {
    computed: {
        listFilterOptions() {
            const filterOptions = this.$super('listFilterOptions');

            Object.assign(filterOptions, {
                'has-easy-coupon-product-filter': {
                    property: 'lineItems.product.netiEasyCouponProduct.id',
                    label: this.$t('neti-easy-coupon.order-list.sidebar-filter.hasVoucherProductLabel'),
                },
                'has-easy-coupon-filter': {
                    property: 'lineItems.payload.easyCouponTransactionId',
                    label: this.$t('neti-easy-coupon.order-list.sidebar-filter.hasVoucherLabel'),
                },
            });

            return filterOptions;
        },
    },

    created() {
        this.defaultFilters.push('has-easy-coupon-product-filter', 'has-easy-coupon-filter');
    },
});