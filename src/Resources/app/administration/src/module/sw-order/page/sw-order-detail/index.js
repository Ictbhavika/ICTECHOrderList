const { Utils } = Shopware;
const { array } = Utils;

Shopware.Component.override('sw-order-detail', {

    computed: {
        deliveryDiscounts() {
            const shippingCostReduction = [];
            const decimalRounding= this.order.currency.itemRounding.decimals;

            // Collect all shipping reductions of EC vouchers
            this.order.lineItems.forEach(lineItem => {
                if (0 === lineItem.children.count) {
                    return;
                }

                lineItem.children.forEach(children => {
                    if ('easy-coupon-child-shipping-costs' !== children.type) {
                        return;
                    }

                    const shippingCostPrice = children.payload['easy-coupon-shipping-costs-price'];

                    shippingCostReduction.push(parseFloat(shippingCostPrice).toFixed(decimalRounding));
                });
            });

            // If no shipping reductions of EC are available, reduce deliveries by SW parent method.
            if (0 === shippingCostReduction.length) {
                return this.$super('deliveryDiscounts');
            }

            // Retain deliveries which are reduced by EC vouchers
            this.order.deliveries.forEach((delivery, index) => {
                if (index < 1) {
                    return;
                }

                shippingCostReduction.forEach((reduction, index) => {
                    if (reduction === delivery.shippingCosts.totalPrice) {
                        return;
                    }

                    array.slice(shippingCostReduction, index, 1);
                });
            });

            return shippingCostReduction;
        },
    },
});
