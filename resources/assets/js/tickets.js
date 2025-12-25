import './bootstrap';
import { createApp } from 'vue';
import TicketWidget from './components/tickets/TicketWidget.vue';
import TicketModal from './components/tickets/TicketModal.vue';
import store from './components/tickets/store.js';

const widgetContainer = document.getElementById('ticket-widget-container');
if (widgetContainer) {
    const app = createApp(TicketWidget);
    app.mount('#ticket-widget-container');
}

const modalContainer = document.getElementById('ticket-modal-container');
if (modalContainer) {
    const app = createApp(TicketModal);
    app.mount('#ticket-modal-container');

    store.startUnreadPolling();
}
