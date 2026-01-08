<template>
  <div>
    <div
      v-if="isOpen"
      class="ticket-modal-overlay"
      @click.self="close"
    >
      <div class="ticket-modal">
        <div class="ticket-modal-header">
          <h4>
            <i class="fa fa-life-ring m-r-sm"></i>
            Help Center
          </h4>
          <button class="ticket-modal-close" @click="close">
            <i class="fa fa-times"></i>
          </button>
        </div>
        <div class="ticket-modal-body">
          <ticket-widget ref="widget" :in-modal="true" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import TicketWidget from './TicketWidget.vue';
import store from './store.js';

export default {
  components: {
    TicketWidget,
  },

  data() {
    return {
      isOpen: false,
    };
  },

  mounted() {
    window.openTicketModal = this.open;
    window.openTicketModalWithTicket = this.openWithTicket;
    window.closeTicketModal = this.close;

    document.addEventListener('keydown', this.handleEscape);

    this.checkUrlForTicket();
  },

  beforeUnmount() {
    document.removeEventListener('keydown', this.handleEscape);
  },

  methods: {
    open() {
      this.isOpen = true;
      document.body.classList.add('ticket-modal-open');
      store.loadTickets().then(() => {
        store.markAllSeen();
      });
      store.loadTicketTypes();
    },

    openWithTicket(ticketId) {
      this.isOpen = true;
      document.body.classList.add('ticket-modal-open');
      store.loadTickets();
      store.loadTicketTypes();
      store.setView('detail', ticketId);
    },

    close() {
      this.isOpen = false;
      document.body.classList.remove('ticket-modal-open');
      store.stopPolling();
      store.markAllSeen();
      this.clearUrlTicket();
    },

    handleEscape(e) {
      if (e.key === 'Escape' && this.isOpen) {
        this.close();
      }
    },

    checkUrlForTicket() {
      const params = new URLSearchParams(window.location.search);
      const ticketId = params.get('ticket');
      if (ticketId) {
        this.openWithTicket(parseInt(ticketId, 10));
      }
    },

    clearUrlTicket() {
      const url = new URL(window.location);
      if (url.searchParams.has('ticket')) {
        url.searchParams.delete('ticket');
        window.history.replaceState({}, '', url);
      }
    },
  },
};
</script>

<style scoped>
.ticket-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  z-index: 9999;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 40px 20px;
  overflow-y: auto;
}

.ticket-modal {
  background: var(--color-bg-dark);
  border-radius: 10px;
  width: 100%;
  max-width: 900px;
  max-height: calc(100vh - 80px);
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

.ticket-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid var(--overlay-light);
  flex-shrink: 0;
}

.ticket-modal-header h4 {
  margin: 0;
  color: var(--color-white);
  font-weight: 600;
}

.ticket-modal-close {
  background: none;
  border: none;
  color: var(--color-muted);
  font-size: 18px;
  cursor: pointer;
  padding: 5px;
  transition: color 0.2s;
}

.ticket-modal-close:hover {
  color: var(--color-white);
}

.ticket-modal-body {
  padding: 20px;
  overflow-y: auto;
  flex: 1;
}
</style>

<style>
body.ticket-modal-open {
  overflow: hidden;
}
</style>
