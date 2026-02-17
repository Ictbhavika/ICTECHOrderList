import Plugin from 'src/plugin-system/plugin.class';

export default class OrderListViewSwitcherPlugin extends Plugin {

    static options = {
        gridViewSelector: '[data-view-mode="grid"]',
        listViewSelector: '[data-view-mode="list"]',
        containerSelector: '[data-order-list-container]',
        storageKey: 'orderListViewMode',
        activeClass: 'active',
        gridClass: 'view-grid',
        listClass: 'view-list'
    };

    init() {
        this._gridBtn = document.querySelector(this.options.gridViewSelector);
        this._listBtn = document.querySelector(this.options.listViewSelector);
        this._container = document.querySelector(this.options.containerSelector);

        if (!this._gridBtn || !this._listBtn || !this._container) return;

        this._loadViewMode();
        this._registerEvents();
    }

    _registerEvents() {
        this._gridBtn.addEventListener('click', this._onGridView.bind(this));
        this._listBtn.addEventListener('click', this._onListView.bind(this));
    }

    _onGridView(event) {
        event.preventDefault();
        this._setViewMode('grid');
    }

    _onListView(event) {
        event.preventDefault();
        this._setViewMode('list');
    }

    _setViewMode(mode) {
        this._container.classList.remove(this.options.gridClass, this.options.listClass);
        this._container.classList.add(mode === 'grid' ? this.options.gridClass : this.options.listClass);

        this._gridBtn.classList.toggle(this.options.activeClass, mode === 'grid');
        this._listBtn.classList.toggle(this.options.activeClass, mode === 'list');

        localStorage.setItem(this.options.storageKey, mode);
    }

    _loadViewMode() {
        const savedMode = localStorage.getItem(this.options.storageKey) || 'grid';
        this._setViewMode(savedMode);
    }
}
