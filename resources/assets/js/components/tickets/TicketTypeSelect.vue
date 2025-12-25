<template>
  <div class="ticket-type-select">
    <div class="text-center m-b-lg">
      <h4 class="text-white">What do you need help with?</h4>
      <p class="text-muted">Select the category that best describes your request</p>
    </div>

    <div v-if="store.loading.ticketTypes" class="text-center p-lg">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
      <p class="m-t-md text-muted">Loading options...</p>
    </div>

    <div v-else-if="store.errors.ticketTypes" class="alert alert-danger">
      <i class="fa fa-exclamation-circle m-r-sm"></i>
      {{ store.errors.ticketTypes }}
      <button class="btn btn-sm btn-outline-danger pull-right" @click="store.loadTicketTypes()">
        <i class="fa fa-refresh"></i> Retry
      </button>
    </div>

    <div v-else class="type-list">
      <a
        v-for="type in store.ticketTypes"
        :key="type.id"
        href="#"
        class="panel panel-filled type-card"
        @click.prevent="selectType(type)"
      >
        <div class="panel-body">
          <div class="type-icon">
            <i :class="getIconForType(type.slug)"></i>
          </div>
          <div class="type-content">
            <h5 class="type-name">{{ type.name }}</h5>
            <p class="type-description">{{ type.description }}</p>
          </div>
          <div class="type-arrow">
            <i class="fa fa-chevron-right"></i>
          </div>
        </div>
      </a>
    </div>

    <div v-if="!store.loading.ticketTypes && store.ticketTypes.length === 0" class="text-center p-lg">
      <i class="fa fa-folder-open fa-3x text-muted m-b-md"></i>
      <h5 class="text-white">No Options Available</h5>
      <p class="text-muted">There are no ticket types available for your role.</p>
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
    selectType(type) {
      store.setView('create', type);
    },

    getIconForType(slug) {
      const icons = {
        'forum-change': 'fa fa-edit',
        'awards-medals': 'fa fa-trophy',
        'member-rename': 'fa fa-id-card',
        'teamspeak-change': 'fa fa-headphones',
        'misc': 'fa fa-question-circle',
      };
      return icons[slug] || 'fa fa-tag';
    },
  },
};
</script>

<style scoped>
.ticket-type-select {
  padding: 10px 0;
}

.type-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.type-card {
  margin-bottom: 0;
}

.type-card .panel-body {
  display: flex;
  align-items: center;
  padding: 15px 20px;
}

.type-icon {
  width: 48px;
  height: 48px;
  background: var(--color-accent);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  flex-shrink: 0;
}

.type-icon i {
  color: var(--color-white);
  font-size: 20px;
}

.type-content {
  flex: 1;
  min-width: 0;
}

.type-name {
  font-size: 16px;
  font-weight: 600;
  color: var(--color-white);
  margin: 0 0 4px 0;
}

.type-description {
  font-size: 13px;
  color: var(--color-muted);
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.type-arrow {
  color: var(--color-muted);
  margin-left: 15px;
  transition: transform 0.2s, color 0.2s;
}

.type-card:hover .type-arrow {
  color: var(--color-accent);
  transform: translateX(4px);
}

.p-lg {
  padding: 40px;
}
</style>
