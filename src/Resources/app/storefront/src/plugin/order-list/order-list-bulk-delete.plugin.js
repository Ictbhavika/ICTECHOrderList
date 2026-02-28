import Plugin from 'src/plugin-system/plugin.class';
import DomAccess from 'src/helper/dom-access.helper';

export default class OrderListBulkDeletePlugin extends Plugin {
    init() {
        this.selectAllCheckbox = DomAccess.querySelector(this.el, '[data-select-all]', false);
        this.productCheckboxes = Array.from(this.el.querySelectorAll('[data-product-checkbox]'));
        this.bulkRemoveBtn = DomAccess.querySelector(this.el, '[data-bulk-remove-btn]', false);
        this.selectedCountEl = DomAccess.querySelector(this.el, '[data-selected-count]', false);
        this.modal = document.querySelector('#bulkDeleteModal');
        this.modalInstance = null;

        this._registerEvents();
    }

    _registerEvents() {
        if (this.selectAllCheckbox) {
            this.selectAllCheckbox.addEventListener('change', this._onSelectAllChange.bind(this));
        }

        if (this.productCheckboxes && this.productCheckboxes.length) {
            this.productCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', this._onCheckboxChange.bind(this));
            });
        }

        if (this.bulkRemoveBtn) {
            this.bulkRemoveBtn.addEventListener('click', this._onBulkRemove.bind(this));
        }
    }

    _onSelectAllChange(event) {
        const isChecked = event.currentTarget.checked;
        if (this.productCheckboxes && this.productCheckboxes.length) {
            this.productCheckboxes.forEach(cb => cb.checked = isChecked);
        }
        this._updateBulkButton();
    }

    _onCheckboxChange() {
        if (this.productCheckboxes.length && this.selectAllCheckbox) {
            const allChecked = this.productCheckboxes.every(cb => cb.checked);
            this.selectAllCheckbox.checked = allChecked;
        }
        this._updateBulkButton();
    }

    _onBulkRemove(event) {
        event.preventDefault();
        const count = this.productCheckboxes.filter(cb => cb.checked).length;
        if (count > 0 && this.modal) {
            if (!this.modalInstance) {
                this.modalInstance = new bootstrap.Modal(this.modal);
            }
            const countEl = this.modal.querySelector('[data-bulk-count]');
            if (countEl) countEl.textContent = count;
            this.modalInstance.show();
        }
    }

    _updateBulkButton() {
        if (!this.productCheckboxes.length || !this.bulkRemoveBtn) return;
        
        const count = this.productCheckboxes.filter(cb => cb.checked).length;
        if (this.selectedCountEl) {
            this.selectedCountEl.textContent = `${count} selected`;
        }
        this.bulkRemoveBtn.disabled = count === 0;
    }
}
