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

        // prefer the dedicated CSV upload form if present (avoids nested-form issues)
        const csvForm = DomAccess.querySelector(this.el, '#ictech-orderlist-csv-upload-form', false);
        const form = csvForm || this.el.closest('form') || DomAccess.querySelector(this.el, 'form', false);
        const url = (form && form.action) ? form.action : '/order-list/create';
        const formData = form ? new FormData(form) : new FormData();

        console.log("url",url);
        
        formData.append('csvFile', file);
        // const formData = new FormData(this.csvForm);
       // ElementLoadingIndicatorUtil.create(this.el);
       // ElementLoadingIndicatorUtil.start(this.el);

        const httpClient = new HttpClient();
        httpClient.post(url, formData, (responseText, request) => {
            if (request && request.status >= 400) {
                console.error('Error uploading CSV:', responseText);
                alert('There was an error uploading the CSV file. Please try again.');
                return;
            }

            try {
                const response = JSON.parse(responseText);
                
                if (response.success) {
                    console.log('Order list created successfully:', response);
                    alert(response.message || 'Order list created successfully');
                    PseudoModalUtil.close(this.el);
                    // Optional: refresh the page after a short delay
                    setTimeout(() => window.location.reload(), 500);
                } else {
                    console.error('Error creating order list:', response.error);
                    alert('Error: ' + (response.error || 'Unknown error occurred'));
                }
            } catch (e) {
                console.error('Error parsing response:', e, responseText);
                alert('An unexpected error occurred. Please try again.');
            }
        });
    }
}