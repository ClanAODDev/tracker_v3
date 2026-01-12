import './bootstrap';
import { createApp } from 'vue';
import TicketWidget from './components/tickets/TicketWidget.vue';
import store from './components/tickets/store.js';

const widgetContainer = document.getElementById('ticket-widget-container');
if (widgetContainer) {
    const initialTicketId = widgetContainer.dataset.ticketId;
    const initialView = widgetContainer.dataset.initialView;

    store.loadTickets();
    store.loadTicketTypes();

    if (initialTicketId) {
        store.setView('detail', parseInt(initialTicketId, 10));
    } else if (initialView) {
        store.setView(initialView);
    }

    const app = createApp(TicketWidget);
    app.mount('#ticket-widget-container');
}
