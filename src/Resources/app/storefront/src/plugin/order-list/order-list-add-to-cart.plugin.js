import Plugin from 'src/plugin-system/plugin.class';
import HttpClient from 'src/service/http-client.service';

export default class OrderListAddToCartPlugin extends Plugin {
    init() {
        this.client = new HttpClient();
        this._registerEvents();
    }

    _registerEvents() {
        this.el.addEventListener('click', this._onAddAllClick.bind(this));
    }

    _onAddAllClick(event) {
        event.preventDefault();
        const url = this.el.dataset.url;
        
        this.el.disabled = true;
        this.el.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Adding...';

        this.client.post(url, null, (response) => {
            const data = JSON.parse(response);
            if (data.success && data.items) {
                this._addItemsToCart(data.items);
            }
        });
    }

    _addItemsToCart(items) {
        const formData = new FormData();
        
        items.forEach((item, index) => {
            formData.append(`lineItems[${item.id}][id]`, item.id);
            formData.append(`lineItems[${item.id}][quantity]`, item.quantity);
            formData.append(`lineItems[${item.id}][type]`, 'product');
            formData.append(`lineItems[${item.id}][referencedId]`, item.id);
        });

        const cartUrl = window.router['frontend.checkout.line-item.add'];
        this.client.post(cartUrl, formData, () => {
            window.location.href = window.router['frontend.checkout.cart.page'];
        });
    }
}
