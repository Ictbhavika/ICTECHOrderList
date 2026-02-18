import HttpClient from 'src/service/http-client.service';
import DeviceDetection from 'src/helper/device-detection.helper';
import DomAccess from 'src/helper/dom-access.helper';
import ElementLoadingIndicatorUtil from 'src/utility/loading-indicator/element-loading-indicator.util';
import PseudoModalUtil from 'src/utility/modal-extension/pseudo-modal.util';

export default class IctechOrderList extends window.PluginBaseClass {
    
    init() {
        this._registerEvents();
    }

    _registerEvents() {
        const csvUploadInput = DomAccess.querySelector(this.el, '.js-csv-upload', false);

        if (csvUploadInput) {
            csvUploadInput.addEventListener('change', this._handleCsvUpload.bind(this));
        }
    }

    _handleCsvUpload(event) {
        const file = event.target.files[0];
        if (!file) {
            return;
        }

        this._clearErrors();
        const form = this.el.closest('form') || DomAccess.querySelector(this.el, 'form', false);
        const url = (form && form.action) ? form.action : '/order-list/create';
        const formData = form ? new FormData(form) : new FormData();

        const httpClient = new HttpClient();
        httpClient.post(url, formData, (responseText, request) => {
            if (request && request.status >= 400) {
                try {
                    const errorResponse = JSON.parse(responseText);
                    this._handleError(errorResponse.error || errorResponse.errors || responseText);
                } catch (e) {
                    this._handleError(responseText);
                }
                return;
            }

            try {
                const response = JSON.parse(responseText);
                
                if (response.success) {
                    this._showSuccessMessage(response.message || 'Order list created successfully');
                    this._clearForm();
                    if (response.orderListId) {
                        this._loadProducts(response.orderListId);
                    }
                } else {
                    this._handleError(response.error || response.errors);
                }
            } catch (e) {
                this._handleError({csv: 'Invalid response from server'});
            }
        });
    }

    _handleError(error) {
        const form = document.getElementById('ictech-orderlist-csv-upload-form');
        if (!form) return;

        if (typeof error === 'object' && error !== null) {
            if (error.orderListName) {
                const nameInput = form.querySelector('.js-orderlist-name');
                const nameError = form.querySelector('.js-orderlist-error');
                const csvInput = form.querySelector('.js-csv-upload');
                if (nameInput && nameError) {
                    nameInput.classList.add('is-invalid');
                    nameError.textContent = error.orderListName;
                    nameError.style.display = 'block';
                }
                if (csvInput) csvInput.value = '';
            }
            if (error.csv || error.file) {
                const csvInput = form.querySelector('.js-csv-upload');
                const csvError = form.querySelector('.js-csv-error');
                if (csvInput && csvError) {
                    csvInput.classList.add('is-invalid');
                    csvError.textContent = error.csv || error.file;
                    csvError.style.display = 'block';
                }
            }
        } else if (typeof error === 'string') {
            if (error.toLowerCase().includes('order') && error.toLowerCase().includes('name')) {
                const nameInput = form.querySelector('.js-orderlist-name');
                const nameError = form.querySelector('.js-orderlist-error');
                const csvInput = form.querySelector('.js-csv-upload');
                if (nameInput && nameError) {
                    nameInput.classList.add('is-invalid');
                    nameError.textContent = error;
                    nameError.style.display = 'block';
                }
                if (csvInput) csvInput.value = '';
            } else {
                const csvInput = form.querySelector('.js-csv-upload');
                const csvError = form.querySelector('.js-csv-error');
                if (csvInput && csvError) {
                    csvInput.classList.add('is-invalid');
                    csvError.textContent = error;
                    csvError.style.display = 'block';
                }
            }
        }
    }

    _clearErrors() {
        const form = document.getElementById('ictech-orderlist-csv-upload-form');
        if (!form) return;

        const nameInput = form.querySelector('.js-orderlist-name');
        const nameError = form.querySelector('.js-orderlist-error');
        const csvInput = form.querySelector('.js-csv-upload');
        const csvError = form.querySelector('.js-csv-error');
        
        if (nameInput) nameInput.classList.remove('is-invalid');
        if (nameError) {
            nameError.textContent = '';
            nameError.style.display = 'none';
        }
        if (csvInput) csvInput.classList.remove('is-invalid');
        if (csvError) {
            csvError.textContent = '';
            csvError.style.display = 'none';
        }
        
        const successAlert = form.querySelector('.js-success-alert');
        if (successAlert) {
            successAlert.style.display = 'none';
        }
    }

    _showSuccessMessage(message) {
        const form = document.getElementById('ictech-orderlist-csv-upload-form');
        if (!form) return;

        let successAlert = form.querySelector('.js-success-alert');
        if (!successAlert) {
            successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success js-success-alert mt-3';
            form.insertBefore(successAlert, form.firstChild);
        }
        
        successAlert.textContent = message;
        successAlert.style.display = 'block';
        
        setTimeout(() => {
            if (successAlert) {
                successAlert.style.display = 'none';
            }
        }, 5000);
    }

    _clearForm() {
        const form = document.getElementById('ictech-orderlist-csv-upload-form');
        if (!form) return;

        const nameInput = form.querySelector('.js-orderlist-name');
        const csvInput = form.querySelector('.js-csv-upload');
        
        if (nameInput) nameInput.value = '';
        if (csvInput) csvInput.value = '';
    }

    _loadProducts(orderListId) {
        const httpClient = new HttpClient();
        httpClient.get(`/order-list/${orderListId}/products`, (responseText) => {
            try {
                const response = JSON.parse(responseText);
                if (response.success && response.products) {
                    this._displayProducts(response.products);
                }
            } catch (e) {
                console.error('Failed to load products', e);
            }
        });
    }

    _displayProducts(products) {
        const tbody = document.getElementById('orderlist-products');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!products || products.length === 0) {
            tbody.innerHTML = `
                <tr class="empty-state">
                    <td colspan="5" class="text-center py-5 text-muted">
                        <div class="fw-semibold mb-1">No product added yet</div>
                        <small>Search or import products to begin your order.</small>
                    </td>
                </tr>
            `;
            return;
        }

        products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        ${product.image ? `<img src="${product.image}" alt="${product.name}" style="width: 40px; height: 40px; object-fit: cover;" class="me-2">` : ''}
                        <div>
                            <div class="fw-semibold">${product.name || product.productNumber}</div>
                            <small class="text-muted">${product.productNumber}</small>
                        </div>
                    </div>
                </td>
                <td>${product.quantity || 1}</td>
                <td>${product.price || '-'}</td>
                <td>${product.total || '-'}</td>
                <td>
                    <button class="btn btn-sm btn-outline-danger" onclick="removeProduct('${product.id}')">
                        <svg width="14" height="14" fill="currentColor"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }
}