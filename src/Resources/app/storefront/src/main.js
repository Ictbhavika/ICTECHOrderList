window.PluginManager.register('IctechOrderList', () => import('./plugin/ictech-order-list/ictech-order-list-form.plugin'), '[data-ictech-order-list-form]');
window.PluginManager.register('OrderListViewSwitcher', () => import('./plugin/order-list/order-list-view-switcher.plugin'), '[data-order-list-view-switcher]');
window.PluginManager.register('OrderListSearch', () => import('./plugin/order-list/order-list-search.plugin'), '[data-order-list-search]');
window.PluginManager.register('OrderListCsvUpload', () => import('./plugin/order-list/order-list-csv-upload.plugin'), '[data-order-list-csv-upload]');
window.PluginManager.register('FlashMessage', () => import('./plugin/order-list/flash-message.plugin'), '[data-flash-message]');
window.PluginManager.register('OrderListModal', () => import('./plugin/order-list/order-list-modal.plugin'), '[data-order-list-modals]');
