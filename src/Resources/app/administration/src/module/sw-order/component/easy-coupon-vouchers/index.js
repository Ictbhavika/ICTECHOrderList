import template from './template.html.twig';
import './style.scss';

const { Component, Utils } = Shopware;
const { format }           = Utils;

Component.register('neti-easy-coupon-order-detail-vouchers', {
    template,

    props: {
        order: {
            type: Object,
            required: true
        },
        data: {
            type: Array,
            required: true
        },
        isLoading: {
            type: Boolean,
            required: false
        }
    },

    computed: {
        redeemedItems() {
            return this.allItems.filter(i => i._type !== 'purchasableVoucher');
        },

        purchasedItems() {
            return this.allItems.filter(i => i._type === 'purchasableVoucher');
        },

        allItems() {
            const data = [];

            this.data.forEach(lineItem => {
                const type = this.getVoucherType(lineItem);

                lineItem._type = type;

                if ('purchasableVoucher' === type) {
                    const vouchers = lineItem.payload.netiNextEasyCoupon.vouchers || [];

                    if (!vouchers.length) {
                        data.push(lineItem);
                    }

                    let designTitle = null;

                    if (
                        'netiNextEasyCouponDesigns' in lineItem.payload
                        && 'designTitle' in lineItem.payload.netiNextEasyCouponDesigns
                    ) {
                        designTitle = lineItem.payload.netiNextEasyCouponDesigns.designTitle;
                    }

                    vouchers.map(voucher => {
                        return {
                            ...lineItem,
                            /**
                             * This is only used to prevent the following warning from Vue
                             * Duplicate keys detected: 'd5dd7c20b9d24ce0890435818278ca6a'. This may cause an update
                             * error.
                             */
                            id: voucher.id,
                            designTitle,
                            // The payload need to be created like this because of reference hell.
                            payload: {
                                ...lineItem.payload,
                                netiNextEasyCoupon: {
                                    ...lineItem.payload.netiNextEasyCoupon,
                                    voucherId: voucher.id,
                                    code: voucher.code
                                }
                            }
                        };
                    }).forEach(lineItem => data.push(lineItem));
                } else {
                    data.push(lineItem);
                }
            });

            return data;
        }
    },

    methods: {
        getVoucherType(lineItem) {
            if (lineItem.payload.discountScope === 'netiEasyCoupon') {
                return 'voucher';
            }

            if ('netiNextEasyCoupon' in lineItem.payload) {
                return 'purchasableVoucher';
            }

            return 'invalid';
        }
    }
});
