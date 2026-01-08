<template>
  <div class="ticket-widget">
    <div v-if="!inModal" class="view-header">
      <div class="header-icon">
        <i class="pe pe-7s-help1"></i>
      </div>
      <div class="header-title">
        <h3 class="m-b-xs">
          Help Center
          <button
            v-if="store.currentView !== 'list'"
            class="btn btn-default btn-sm pull-right"
            @click="store.setView('list')"
          >
            <i class="fa fa-arrow-left"></i> Back
          </button>
          <button
            v-else
            class="btn btn-primary btn-sm pull-right"
            @click="showTypeSelector"
          >
            <i class="fa fa-plus"></i> New Ticket
          </button>
        </h3>
        <small class="slight">Submit and track your support requests</small>
      </div>
      <hr/>
    </div>

    <div v-if="inModal" class="modal-actions m-b-md">
      <div class="actions-row">
        <button
          v-if="store.currentView !== 'list'"
          class="btn btn-default btn-sm"
          @click="store.setView('list')"
        >
          <i class="fa fa-arrow-left"></i> Back
        </button>

        <div v-else-if="store.isAdmin" class="view-toggle">
          <div class="toggle-track" :class="{ 'admin-active': store.viewMode === 'admin' }">
            <div class="toggle-indicator"></div>
            <button
              class="toggle-btn"
              :class="{ active: store.viewMode === 'user' }"
              @click="store.setViewMode('user')"
            >
              <i class="fa fa-user m-r-xs"></i>
              <span class="toggle-label">My Tickets</span>
            </button>
            <button
              class="toggle-btn"
              :class="{ active: store.viewMode === 'admin' }"
              @click="store.setViewMode('admin')"
            >
              <i class="fa fa-shield m-r-xs"></i>
              <span class="toggle-label">Admin Queue</span>
            </button>
          </div>
        </div>

        <button
          v-if="store.currentView === 'list' && store.viewMode === 'user'"
          class="btn btn-primary btn-sm ml-auto"
          @click="showTypeSelector"
        >
          <i class="fa fa-plus"></i> New Ticket
        </button>
      </div>
    </div>

    <div v-if="store.currentView === 'list' && displayTickets.length > 0" class="row quick-stats m-b-md">
      <div class="col-md-3 col-sm-6">
        <div class="panel panel-filled">
          <div class="panel-body">
            <div class="stat-icon">
              <i class="fa fa-life-ring text-info"></i>
            </div>
            <div class="stat-content">
              <span class="stat-value">{{ displayTickets.length }}</span>
              <span class="stat-label">Total</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="panel panel-filled">
          <div class="panel-body">
            <div class="stat-icon">
              <i class="fa fa-envelope-open text-primary"></i>
            </div>
            <div class="stat-content">
              <span class="stat-value">{{ openCount }}</span>
              <span class="stat-label">Open</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="panel panel-filled">
          <div class="panel-body">
            <div class="stat-icon">
              <i class="fa fa-spinner text-warning"></i>
            </div>
            <div class="stat-content">
              <span class="stat-value">{{ assignedCount }}</span>
              <span class="stat-label">In Progress</span>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6">
        <div class="panel panel-filled">
          <div class="panel-body">
            <div class="stat-icon">
              <i class="fa fa-check-circle text-success"></i>
            </div>
            <div class="stat-content">
              <span class="stat-value">{{ resolvedCount }}</span>
              <span class="stat-label">Resolved</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="panel panel-filled">
      <div class="panel-body">
        <transition name="fade" mode="out-in">
          <ticket-type-select
            v-if="store.currentView === 'select-type'"
            key="select-type"
          />

          <ticket-form
            v-else-if="store.currentView === 'create'"
            key="create"
          />

          <ticket-list
            v-else-if="store.currentView === 'list'"
            key="list"
          />

          <ticket-detail
            v-else-if="store.currentView === 'detail'"
            key="detail"
          />
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
import store from './store.js';
import TicketTypeSelect from './TicketTypeSelect.vue';
import TicketForm from './TicketForm.vue';
import TicketList from './TicketList.vue';
import TicketDetail from './TicketDetail.vue';

export default {
  components: {
    TicketTypeSelect,
    TicketForm,
    TicketList,
    TicketDetail,
  },

  props: {
    inModal: {
      type: Boolean,
      default: false,
    },
  },

  data() {
    return {
      store,
    };
  },

  computed: {
    displayTickets() {
      return store.viewMode === 'admin' ? store.adminTickets : store.tickets;
    },
    openCount() {
      return this.displayTickets.filter(t => t.state === 'new').length;
    },
    assignedCount() {
      return this.displayTickets.filter(t => t.state === 'assigned').length;
    },
    resolvedCount() {
      return this.displayTickets.filter(t => t.state === 'resolved' || t.state === 'rejected').length;
    },
  },

  mounted() {
    if (!this.inModal) {
      store.loadTickets();
      store.loadTicketTypes();
    }
  },

  methods: {
    showTypeSelector() {
      store.setView('select-type');
    },
  },
};
</script>

<style scoped>
.ticket-widget {
  padding: 15px 0;
}

.view-header {
  margin-bottom: 20px;
}

.view-header .header-icon {
  float: left;
  margin-right: 15px;
}

.view-header .header-icon i {
  font-size: 48px;
  color: var(--color-accent);
}

.view-header .header-title h3 {
  color: var(--color-white);
  margin-bottom: 5px;
}

.view-header .header-title .slight {
  color: var(--color-muted);
}

.view-header hr {
  border-top: 1px solid var(--color-border-dark);
  margin-top: 15px;
  clear: both;
}

.quick-stats .panel {
  margin-bottom: 15px;
}

.quick-stats .panel-body {
  display: flex;
  align-items: center;
  padding: 15px;
}

.quick-stats .stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--overlay-dark);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
}

.quick-stats .stat-icon i {
  font-size: 20px;
}

.quick-stats .stat-content {
  flex: 1;
}

.quick-stats .stat-value {
  display: block;
  font-size: 24px;
  font-weight: 600;
  color: var(--color-white);
  line-height: 1.2;
}

.quick-stats .stat-label {
  font-size: 12px;
  text-transform: uppercase;
  color: var(--color-muted);
  letter-spacing: 0.5px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.view-toggle {
  display: flex;
}

.toggle-track {
  display: flex;
  position: relative;
  background: var(--overlay-dark);
  border-radius: 8px;
  padding: 4px;
  border: 1px solid var(--overlay-light);
}

.toggle-indicator {
  position: absolute;
  top: 4px;
  left: 4px;
  width: calc(50% - 4px);
  height: calc(100% - 8px);
  background: linear-gradient(135deg, var(--color-accent), color-mix(in srgb, var(--color-accent) 80%, #000));
  border-radius: 6px;
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.toggle-track.admin-active .toggle-indicator {
  transform: translateX(100%);
}

.toggle-btn {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  color: var(--color-muted);
  padding: 8px 16px;
  font-size: 12px;
  font-weight: 600;
  border-radius: 6px;
  cursor: pointer;
  transition: color 0.2s;
  white-space: nowrap;
}

.toggle-btn:hover {
  color: var(--color-gray-300);
}

.toggle-btn.active {
  color: var(--color-white);
}

.toggle-btn i {
  font-size: 11px;
}

.toggle-label {
  display: inline;
}

@media (max-width: 480px) {
  .toggle-label {
    display: none;
  }

  .toggle-btn {
    padding: 8px 14px;
  }

  .toggle-btn i {
    margin-right: 0 !important;
  }
}

.w-100 {
  width: 100%;
}

.actions-row {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
}

.ml-auto {
  margin-left: auto;
}
</style>
