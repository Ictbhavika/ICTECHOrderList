import template from './template.html.twig';
import './style.scss';

const { Component } = Shopware;

Component.override('sw-order-detail-general', {
    template,

    computed: {
        hasEasyCouponVouchers() {
            return this.easyCouponVouchers.length > 0;
        },

        hasEasyCouponPostalOption() {
            return this.easyCouponPostalOptions.length > 0;
        },

        hasRedeemedEasyCouponVouchers() {
            return this.redeemedEasyCouponVouchers.length > 0;
        },

        easyCouponVouchers() {
            if (!this.order) {
                return [];
            }

            return this.order.lineItems.filter(lineItem => {
                if (typeof lineItem.payload !== 'object' || lineItem.payload === null) {
                    return false;
                }

                // voucher redemption
                if (lineItem.payload.discountScope !== null
                    && lineItem.payload.discountScope === 'netiEasyCoupon') {
                    return true;
                }

                // voucher purchase
                return 'netiNextEasyCoupon' in lineItem.payload
                    && typeof lineItem.payload.netiNextEasyCoupon === 'object'
                    && lineItem.payload.netiNextEasyCoupon.voucherValue !== null;
            });
        },

        redeemedEasyCouponVouchers() {
            if (!this.order) {
                return [];
            }

            return this.order.lineItems.filter(lineItem => {
                if (typeof lineItem.payload !== 'object' || lineItem.payload === null) {
                    return false;
                }

                return lineItem.payload.discountScope !== null
                    && lineItem.payload.discountScope === 'netiEasyCoupon';
            });
        },

        easyCouponPostalOptions() {
            return this.easyCouponVouchers.filter(lineItem => {
                if (0 === lineItem.children.length) {
                    return false;
                }

                const postalItems = lineItem.children.filter(child => {
                    return 'easy-coupon-extra-option-postal' === child.type
                });

                return postalItems.length > 0;
            });
        },
    },
});
