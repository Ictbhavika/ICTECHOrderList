import Plugin from 'src/plugin-system/plugin.class';

export default class OrderListSearchPlugin extends Plugin {

    static options = {
        inputSelector: '[data-search-input]',
        itemSelector: '[data-search-item]'
    };

    init() {
        this._input = this.el.querySelector(this.options.inputSelector);
        if (!this._input) return;

        this._input.addEventListener('input', this._onSearch.bind(this));
    }

    _onSearch() {
        const query = this._input.value.toLowerCase();
        const items = document.querySelectorAll(this.options.itemSelector);

        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    }
}
