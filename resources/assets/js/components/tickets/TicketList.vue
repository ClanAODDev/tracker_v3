<template>
  <div class="ticket-list">
    <div v-if="store.loading.tickets" class="text-center p-lg">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
      <p class="m-t-md text-muted">Loading your tickets...</p>
    </div>

    <div v-else-if="store.errors.tickets" class="alert alert-danger">
      <i class="fa fa-exclamation-circle m-r-sm"></i>
      {{ store.errors.tickets }}
      <button class="btn btn-sm btn-outline-danger pull-right" @click="store.loadTickets()">
        <i class="fa fa-refresh"></i> Retry
      </button>
    </div>

    <div v-else-if="store.tickets.length === 0" class="text-center p-lg">
      <div class="empty-icon">
        <i class="fa fa-inbox"></i>
      </div>
      <h4 class="text-white">No Tickets Yet</h4>
      <p class="text-muted m-b-lg">You haven't submitted any support requests yet.</p>
      <button class="btn btn-primary" @click="$parent.showTypeSelector()">
        <i class="fa fa-plus m-r-xs"></i> Create Your First Ticket
      </button>
    </div>

    <div v-else>
      <h5 class="text-white m-b-md">Your Tickets</h5>

      <div class="ticket-cards">
        <a
          v-for="ticket in store.tickets"
          :key="ticket.id"
          href="#"
          class="panel panel-filled ticket-card"
          :class="'panel-c-' + getStateColor(ticket.state)"
          @click.prevent="viewTicket(ticket)"
        >
          <div class="panel-body">
            <div class="ticket-main">
              <div class="ticket-header-row">
                <span class="ticket-id">#{{ ticket.id }}</span>
                <span class="ticket-type-badge">{{ ticket.type?.name ?? 'Unknown' }}</span>
              </div>
              <p class="ticket-preview">{{ truncateDescription(ticket.description) }}</p>
              <div class="ticket-meta">
                <span class="status-badge" :class="'badge-' + ticket.state">
                  <i :class="getStatusIcon(ticket.state)"></i>
                  {{ formatStatus(ticket.state) }}
                </span>
                <span v-if="ticket.owner" class="meta-item">
                  <i class="fa fa-user-circle text-muted m-r-xs"></i>
                  {{ ticket.owner.name }}
                </span>
                <span class="meta-item meta-time">
                  <i class="fa fa-clock-o text-muted m-r-xs"></i>
                  {{ store.formatRelativeDate(ticket.created_at) }}
                </span>
              </div>
            </div>
            <div class="ticket-arrow">
              <i class="fa fa-chevron-right"></i>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>
</template>

<script>
import store from './store.js';

export default {
  data() {
    return {
      store,
    };
  },

  methods: {
    viewTicket(ticket) {
      store.setView('detail', ticket.id);
    },

    truncateDescription(desc) {
      if (!desc) return '';
      return desc.length > 100 ? desc.substring(0, 100) + '...' : desc;
    },

    formatStatus(state) {
      const labels = {
        new: 'Open',
        assigned: 'In Progress',
        resolved: 'Resolved',
        rejected: 'Closed',
      };
      return labels[state] || state;
    },

    getStatusIcon(state) {
      const icons = {
        new: 'far fa-circle',
        assigned: 'fa fa-spinner',
        resolved: 'fa fa-check-circle',
        rejected: 'fa fa-times-circle',
      };
      return icons[state] || 'fa fa-circle';
    },

    getStateColor(state) {
      const colors = {
        new: 'info',
        assigned: 'warning',
        resolved: 'success',
        rejected: 'danger',
      };
      return colors[state] || 'white';
    },
  },
};
</script>

<style scoped>
.ticket-list {
  padding: 10px 0;
}

.empty-icon {
  width: 80px;
  height: 80px;
  background: var(--overlay-dark);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
}

.empty-icon i {
  font-size: 32px;
  color: var(--color-muted);
}

.ticket-cards {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.ticket-card {
  margin-bottom: 0;
}

.ticket-card .panel-body {
  display: flex;
  align-items: center;
  padding: 15px 20px;
}

.ticket-main {
  flex: 1;
  min-width: 0;
}

.ticket-header-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.ticket-id {
  font-weight: 700;
  color: var(--color-white);
  font-size: 14px;
}

.ticket-type-badge {
  font-size: 11px;
  background: var(--overlay-dark);
  color: var(--color-muted);
  padding: 3px 8px;
  border-radius: 3px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.ticket-preview {
  color: var(--color-muted);
  font-size: 13px;
  margin-bottom: 10px;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.ticket-meta {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-wrap: wrap;
}

.meta-item {
  display: flex;
  align-items: center;
  font-size: 12px;
  color: var(--color-muted);
}

.meta-time {
  margin-left: auto;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 500;
}

.badge-new {
  background: rgba(86, 192, 224, 0.2);
  color: var(--color-info);
}

.badge-assigned {
  background: rgba(246, 168, 33, 0.2);
  color: var(--color-accent);
}

.badge-resolved {
  background: rgba(27, 191, 137, 0.2);
  color: var(--color-success);
}

.badge-rejected {
  background: rgba(219, 82, 75, 0.2);
  color: var(--color-danger);
}

.ticket-arrow {
  color: var(--color-muted);
  margin-left: 15px;
  transition: transform 0.2s, color 0.2s;
}

.ticket-card:hover .ticket-arrow {
  color: var(--color-accent);
  transform: translateX(4px);
}

.p-lg {
  padding: 40px;
}
</style>
