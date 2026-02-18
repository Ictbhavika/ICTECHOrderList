import Plugin from 'src/plugin-system/plugin.class';

export default class OrderListModalPlugin extends Plugin {
    init() {
        this._registerModalEvents();
    }

    _registerModalEvents() {
        const renameModal = document.getElementById('renameModal');
        const duplicateModal = document.getElementById('duplicateModal');
        const deleteModal = document.getElementById('deleteModal');

        if (renameModal) {
            renameModal.addEventListener('show.bs.modal', (e) => {
                const btn = e.relatedTarget;
                document.getElementById('renameListId').value = btn.dataset.listId;
                document.getElementById('renameListName').value = btn.dataset.listName;
            });
        }

        if (duplicateModal) {
            duplicateModal.addEventListener('show.bs.modal', (e) => {
                const btn = e.relatedTarget;
                document.getElementById('duplicateListId').value = btn.dataset.listId;
                document.getElementById('duplicateListName').value = btn.dataset.listName + ' (Copy)';
            });
        }

        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', (e) => {
                const btn = e.relatedTarget;
                document.getElementById('deleteListId').value = btn.dataset.listId;
                document.getElementById('deleteListName').textContent = btn.dataset.listName;
            });
        }
    }
}
