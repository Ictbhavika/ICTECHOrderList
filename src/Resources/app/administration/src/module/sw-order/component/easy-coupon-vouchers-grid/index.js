import template from './template.html.twig';

const { Component, Utils } = Shopware;
const { format }           = Utils;

Component.register('neti-easy-coupon-order-detail-vouchers-grid', {
    template,

    props: {
        title: {
            type: String,
            required: true,
        },
        data: {
            type: Array,
            required: true
        },
        order: {
            type: Object,
            required: true
        },
        type: {
            type: String,
            required: true,
        },
        isLoading: {
            type: Boolean,
            required: false,
            default: false
        }
    },

    computed: {
        columns() {
            return this.getColumns();
        },

        currencyFilter() {
            return Shopware.Filter.getByName('currency');
        }
    },

    methods: {
        getColumns() {
            const columns = [
                {
                    property: 'name',
                    dataIndex: 'name',
                    label: this.$t('neti-easy-coupon.order-detail.voucher-list.column.name'),
                },
                {
                    property: 'code',
                    dataIndex: 'code',
                    label: this.$t('neti-easy-coupon.order-detail.voucher-list.column.code'),
                },
                {
                    property: 'value',
                    dataIndex: 'value',
                    label: this.$t('neti-easy-coupon.order-detail.voucher-list.column.value')
                }
            ];

            if (this.type === 'purchased') {
                columns.splice(3, 0, {
                    property: 'postal',
                    dataIndex: 'postal',
                    label: this.$t('neti-easy-coupon.order-detail.voucher-list.column.postal'),
                });


                if (this.data.some(value => this.isValidValue(value?.payload?.netiNextEasyCoupon?.recipientMail))) {
                    columns.push({
                        property: 'payload.netiNextEasyCoupon.recipientMail',
                        dataIndex: 'recipientMail',
                        label: this.$t('neti-easy-coupon.order-detail.voucher-list.column.recipientMail'),
                    });
                }

                if (this.data.some(value => this.isValidValue(value?.payload?.netiNextEasyCoupon?.recipientName))) {
                    columns.push({
                        property: 'payload.netiNextEasyCoupon.recipientName',
                        dataIndex: 'recipientName',
                        label: this.$t('neti-easy-coupon.order-detail.voucher-list.column.recipientName'),
                    });
                }

                if (this.data.some(value => this.isValidValue(value?.payload?.netiNextEasyCoupon?.deliveryMessage))) {
                    columns.push({
                        property: 'payload.netiNextEasyCoupon.deliveryMessage',
                        dataIndex: 'deliveryMessage',
                        label: this.$t('neti-easy-coupon.order-detail.voucher-list.column.deliveryMessage'),
                    });
                }

                if (this.data.some(value => this.isValidValue(value?.payload?.netiNextEasyCoupon?.deliveryDate))) {
                    columns.push({
                        property: 'payload.netiNextEasyCoupon.deliveryDate',
                        dataIndex: 'deliveryDate',
                        label: this.$t('neti-easy-coupon.order-detail.voucher-list.column.deliveryDate'),
                    });
                }
            }

            if (this.type === 'redeemed') {
                columns.splice(3, 0, {
                    property: 'shippingCostReduction',
                    dataIndex: 'shippingCostReduction',
                    label: this.$t('neti-easy-coupon.order-detail.voucher-list.column.shippingCostReduction'),
                });
            }

            return columns;
        },

        getVoucherType(lineItem) {
            if (lineItem.payload.discountScope === 'netiEasyCoupon') {
                return 'voucher';
            }

            if ('netiNextEasyCoupon' in lineItem.payload) {
                return 'purchasableVoucher';
            }

            return 'invalid';
        },

        getValue(lineItem) {
            const voucherType = this.getVoucherType(lineItem);

            if ('voucher' === voucherType) {
                return lineItem.unitPrice;
            } else if ('purchasableVoucher' === voucherType) {
                if (0 === lineItem.children.length) {
                    return lineItem.unitPrice;
                }

                const voucherItem = lineItem.children.filter(child => {
                    return 'easy-coupon-extra-option-voucher' === child.type;
                });

                if (0 === voucherItem.length) {
                    return lineItem.unitPrice;
                }

                return voucherItem[0].unitPrice;
            }
        },

        getCode(lineItem) {
            return lineItem.payload.code || lineItem.payload.netiNextEasyCoupon.code;
        },

        getPostalValue(lineItem) {
            if (this.getVoucherType(lineItem) !== 'purchasableVoucher') {
                return '-';
            }

            return this.hasPostalOption(lineItem)
                ? this.$t('neti-easy-coupon.order-detail.voucher-list.postal-value.yes')
                : this.$t('neti-easy-coupon.order-detail.voucher-list.postal-value.no');
        },

        hasPostalOption(lineItem) {
            if (0 === lineItem.children.length) {
                return false;
            }

            const postalItems = lineItem.children.filter(child => {
                return 'easy-coupon-extra-option-postal' === child.type;
            });

            return postalItems.length > 0;
        },

        getShippingCostReductionValue(lineItem) {
            if (lineItem.payload.reduceShippingCosts) {
                const childItem = lineItem.children.find(c => c.payload['easy-coupon-shipping-costs-price']);

                if (childItem) {
                    let value = childItem.payload['easy-coupon-shipping-costs-price'];

                    value = Math.round(value * 1000) / 1000;

                    return format.currency(
                        value,
                        this.order.currency.shortName
                    );
                }
            }

            return '-';
        },

        getVoucherIdFromLineItem(lineItem) {
            const type    = this.getVoucherType(lineItem);
            let voucherId = null;

            if (type === 'voucher') {
                voucherId = lineItem.payload.discountId;
            } else if (type === 'purchasableVoucher' || 'netiNextEasyCoupon' in lineItem.payload) {
                voucherId = lineItem.payload.netiNextEasyCoupon.voucherId;
            }

            return voucherId;
        },

        onOpenVoucher(lineItem) {
            let voucherId = this.getVoucherIdFromLineItem(lineItem);

            if (null !== voucherId && undefined !== voucherId) {
                this.$router.push(
                    {
                        name: 'neti.easy_coupon.detail',
                        params: {
                            id: voucherId
                        }
                    }
                );
            }
        },

        isValidValue(value) {
            return value !== null && value !== undefined && String(value).trim() !== '';
        }
    }
});
