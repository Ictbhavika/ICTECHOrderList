import Plugin from 'src/plugin-system/plugin.class';

export default class OrderListCsvUploadPlugin extends Plugin {

    static options = {
        uploadInputSelector: '[data-csv-upload-input]',
        uploadTriggerSelector: '[data-csv-upload-trigger]',
        uploadUrl: ''
    };

    init() {
        this._input = this.el.querySelector(this.options.uploadInputSelector);
        this._trigger = this.el.querySelector(this.options.uploadTriggerSelector);
        
        if (!this._input || !this._trigger) return;

        this.options.uploadUrl = this.el.dataset.uploadUrl || this.options.uploadUrl;

        this._trigger.addEventListener('click', () => this._input.click());
        this._input.addEventListener('change', this._onFileChange.bind(this));
    }

    _onFileChange(e) {
        const file = e.target.files[0];
        if (!file) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = this.options.uploadUrl;
        form.enctype = 'multipart/form-data';

        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'csvFile';
        input.files = e.target.files;
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }
}
