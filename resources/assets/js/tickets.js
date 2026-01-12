import './bootstrap';
import { createApp } from 'vue';
import TicketWidget from './components/tickets/TicketWidget.vue';
import store from './components/tickets/store.js';

const widgetContainer = document.getElementById('ticket-widget-container');
if (widgetContainer) {
    const initialTicketId = widgetContainer.dataset.ticketId;
    const initialView = widgetContainer.dataset.initialView;

    store.loadTicketTypes();
    store.initPopstateHandler();

    if (initialTicketId) {
        store.loadTickets();
        store.setView('detail', parseInt(initialTicketId, 10), false);
    } else if (initialView) {
        store.loadTickets();
        store.setView(initialView, null, false);
    } else {
        store.initFromUrl();
        if (store.viewMode === 'user') {
            store.loadTickets();
        }
    }

    const app = createApp(TicketWidget);
    app.mount('#ticket-widget-container');
}
