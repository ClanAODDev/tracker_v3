<template>
  <div class="ticket-widget">
    <div class="view-header">
      <div class="header-icon">
        <i class="pe pe-7s-help1"></i>
      </div>
      <div class="header-title">
        <h3 class="m-b-xs">
          Help Center
        </h3>
        <small class="slight">Submit and track your support requests</small>
      </div>
      <hr/>
    </div>

    <div class="actions-bar m-b-md">
      <div class="actions-row">
        <button
          v-if="store.currentView !== 'list'"
          class="btn btn-default btn-sm"
          @click="store.setView('list')"
        >
          <i class="fa fa-arrow-left"></i> Back
        </button>

        <div v-else-if="store.canWorkTickets" class="view-tabs" ref="tabsContainer">
          <div class="tab-indicator" :style="indicatorStyle"></div>
          <button
            class="tab-btn"
            ref="tabUser"
            :class="{ active: store.viewMode === 'user' }"
            @click="store.setViewMode('user')"
          >
            My Requests
          </button>
          <button
            class="tab-btn"
            ref="tabAssigned"
            :class="{ active: store.viewMode === 'assigned' }"
            @click="store.setViewMode('assigned')"
          >
            Assigned to Me
          </button>
          <button
            class="tab-btn"
            ref="tabAll"
            :class="{ active: store.viewMode === 'all' }"
            @click="store.setViewMode('all')"
          >
            All
          </button>
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

  data() {
    return {
      store,
    };
  },

  computed: {
    displayTickets() {
      if (store.viewMode === 'all') return store.allTickets;
      if (store.viewMode === 'assigned') return store.assignedTickets;
      return store.tickets;
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
    indicatorStyle() {
      const tabRefs = {
        user: this.$refs.tabUser,
        assigned: this.$refs.tabAssigned,
        all: this.$refs.tabAll,
      };
      const activeTab = tabRefs[store.viewMode];
      if (!activeTab || !this.$refs.tabsContainer) {
        return { opacity: 0 };
      }
      const containerRect = this.$refs.tabsContainer.getBoundingClientRect();
      const tabRect = activeTab.getBoundingClientRect();
      return {
        width: `${tabRect.width}px`,
        transform: `translateX(${tabRect.left - containerRect.left - 3}px)`,
        opacity: 1,
      };
    },
  },

  mounted() {
    this.$nextTick(() => this.$forceUpdate());
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

.actions-bar {
  margin-bottom: 15px;
}

.actions-row {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
}

.view-tabs {
  display: flex;
  background: var(--overlay-dark);
  border-radius: 6px;
  padding: 3px;
  gap: 2px;
  position: relative;
}

.tab-indicator {
  position: absolute;
  top: 3px;
  left: 3px;
  height: calc(100% - 6px);
  background: var(--color-accent);
  border-radius: 4px;
  transition: transform 0.25s ease, width 0.25s ease, opacity 0.15s ease;
  pointer-events: none;
  z-index: 0;
}

.tab-btn {
  background: transparent;
  border: none;
  color: var(--color-muted);
  padding: 8px 14px;
  font-size: 11px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-radius: 4px;
  cursor: pointer;
  transition: color 0.2s;
  white-space: nowrap;
  position: relative;
  z-index: 1;
}

.tab-btn:hover {
  color: var(--color-gray-300);
}

.tab-btn.active {
  color: var(--color-white);
}

@media (max-width: 480px) {
  .tab-btn {
    padding: 8px 10px;
    font-size: 10px;
  }
}

.ml-auto {
  margin-left: auto;
}
</style>
