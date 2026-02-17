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
                    PseudoModalUtil.close(this.el);
                    setTimeout(() => window.location.reload(), 500);
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
                if (nameInput && nameError) {
                    nameInput.classList.add('is-invalid');
                    nameError.textContent = error.orderListName;
                    nameError.style.display = 'block';
                }
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
                if (nameInput && nameError) {
                    nameInput.classList.add('is-invalid');
                    nameError.textContent = error;
                    nameError.style.display = 'block';
                }
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
    }
}