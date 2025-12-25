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
      <button
        v-if="store.currentView !== 'list'"
        class="btn btn-default btn-sm"
        @click="store.setView('list')"
      >
        <i class="fa fa-arrow-left"></i> Back
      </button>
      <button
        v-else
        class="btn btn-primary btn-sm"
        @click="showTypeSelector"
      >
        <i class="fa fa-plus"></i> New Ticket
      </button>
    </div>

    <div v-if="store.currentView === 'list' && store.tickets.length > 0" class="row quick-stats m-b-md">
      <div class="col-md-3 col-sm-6">
        <div class="panel panel-filled">
          <div class="panel-body">
            <div class="stat-icon">
              <i class="fa fa-life-ring text-info"></i>
            </div>
            <div class="stat-content">
              <span class="stat-value">{{ store.tickets.length }}</span>
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
    openCount() {
      return store.tickets.filter(t => t.state === 'new').length;
    },
    assignedCount() {
      return store.tickets.filter(t => t.state === 'assigned').length;
    },
    resolvedCount() {
      return store.tickets.filter(t => t.state === 'resolved' || t.state === 'rejected').length;
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
</style>
