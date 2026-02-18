import Plugin from 'src/plugin-system/plugin.class';

export default class FlashMessagePlugin extends Plugin {

    static options = {
        dismissDelay: 5000
    };

    init() {
        const alerts = this.el.querySelectorAll('.alert:not(.alert-permanent)');
        
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, this.options.dismissDelay);
        });
    }
}
