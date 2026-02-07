import './bootstrap';
import { createApp } from 'vue';
import ApplicationWidget from './components/applications/ApplicationWidget.vue';
import store from './components/applications/store.js';

const container = document.getElementById('applications-widget-container');
if (container) {
    const baseUrl = container.dataset.url;
    const canDelete = container.dataset.canDelete === 'true';

    store.init(baseUrl, canDelete);

    const app = createApp(ApplicationWidget);
    app.mount('#applications-widget-container');

    const modal = document.getElementById('applicationsModal');
    if (modal) {
        $(modal).on('shown.bs.modal', () => {
            if (store.applications.length === 0 && !store.loading.applications) {
                store.loadApplications();
            }
        });
    }
}
