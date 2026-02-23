import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';
import DomAccess from 'src/helper/dom-access.helper';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';

export default class OrderListQuantityPlugin extends Plugin {
    init() {
        this.client = new HttpClient();
        this.container = this.el;
        this._registerEvents();
    }

    _registerEvents() {
        const quantitySelectors = this.el.querySelectorAll('[data-order-list-qty]');

        if (quantitySelectors) {
            Array.from(quantitySelectors).forEach(selector => {
                const minusBtn = selector.querySelector('.btn-minus');
                const plusBtn = selector.querySelector('.btn-plus');
                const input = selector.querySelector('.quantity-selector-group-input');

                if (minusBtn) {
                    minusBtn.addEventListener('click', this._onMinusClick.bind(this));
                }
                if (plusBtn) {
                    plusBtn.addEventListener('click', this._onPlusClick.bind(this));
                }
                if (input) {
                    input.addEventListener('change', this._onInputChange.bind(this));
                }
            });
        }
    }

    _onMinusClick(event) {
        const button = event.currentTarget;
        const container = button.closest('[data-order-list-qty]');
        const input = container.querySelector('input');
        const productId = container.dataset.productId;
        const newQty = Math.max(1, parseInt(input.value) - 1);
        input.value = newQty;
        this._updateQuantity(input);
        this._updateHiddenInput(productId, newQty);
    }

    _onPlusClick(event) {
        const button = event.currentTarget;
        const container = button.closest('[data-order-list-qty]');
        const input = container.querySelector('input');
        const productId = container.dataset.productId;
        const newQty = parseInt(input.value) + 1;
        input.value = newQty;
        this._updateQuantity(input);
        this._updateHiddenInput(productId, newQty);
    }

    _onInputChange(event) {
        const input = event.currentTarget;
        const container = input.closest('[data-order-list-qty]');
        const productId = container.dataset.productId;
        input.value = Math.max(1, parseInt(input.value) || 1);
        this._updateQuantity(input);
        this._updateHiddenInput(productId, input.value);
    }

    _updateHiddenInput(productId, qty) {
        const hiddenInput = document.querySelector(`input[data-quantity-hidden="${productId}"]`);
        if (hiddenInput) {
            hiddenInput.value = qty;
        }
    }

    _updateQuantity(input) {
        const itemId = input.id.replace('qty-', '');
        const qty = parseInt(input.value);
        const url = this.el.dataset.updateUrl;

        ElementLoadingIndicatorUtil.create(this.container);

        this.client.post(url, JSON.stringify({ itemId, qty }), () => {
            ElementLoadingIndicatorUtil.remove(this.container);
            window.location.reload();
        });
    }
}
