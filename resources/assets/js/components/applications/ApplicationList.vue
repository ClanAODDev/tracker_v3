<template>
  <div class="application-list">
    <div v-if="store.loading.applications" class="text-center p-lg">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
      <p class="m-t-md text-muted">Loading applications...</p>
    </div>

    <div v-else-if="store.errors.applications" class="alert alert-danger">
      <i class="fa fa-exclamation-circle m-r-sm"></i>
      {{ store.errors.applications }}
      <button class="btn btn-sm btn-outline-danger pull-right" @click="store.loadApplications()">
        <i class="fa fa-refresh"></i> Retry
      </button>
    </div>

    <div v-else-if="store.applications.length === 0" class="text-center p-lg">
      <div class="empty-icon">
        <i class="fab fa-discord"></i>
      </div>
      <h4 class="text-white">No Pending Applications</h4>
      <p class="text-muted m-b-none">There are no pending Discord applications for this division.</p>
    </div>

    <div v-else class="application-cards">
      <a
        v-for="app in store.applications"
        :key="app.id"
        href="#"
        class="panel panel-filled application-card"
        @click.prevent="store.setView('detail', app.id)"
      >
        <div class="panel-body">
          <div class="app-main">
            <div class="app-name">{{ app.discord_username }}</div>
            <div class="app-meta">
              <span class="meta-time">
                <i class="fa fa-clock-o text-muted m-r-xs"></i>
                {{ store.formatRelativeDate(app.created_at) }}
              </span>
              <span v-if="app.comments_count > 0" class="meta-comments">
                <i class="fa fa-comment-o text-muted m-r-xs"></i>
                {{ app.comments_count }}
              </span>
            </div>
          </div>
          <div class="app-arrow">
            <i class="fa fa-chevron-right"></i>
          </div>
        </div>
      </a>
    </div>
  </div>
</template>

<script>
import store from './store.js';

export default {
  data() {
    return { store };
  },
};
</script>

<style scoped>
.application-list {
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

.application-cards {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.application-card {
  margin-bottom: 0;
}

.application-card .panel-body {
  display: flex;
  align-items: center;
  padding: 15px 20px;
}

.app-main {
  flex: 1;
  min-width: 0;
}

.app-name {
  font-weight: 600;
  color: var(--color-white);
  font-size: 14px;
  margin-bottom: 6px;
}

.app-meta {
  display: flex;
  align-items: center;
  gap: 15px;
}

.meta-time,
.meta-comments {
  display: flex;
  align-items: center;
  font-size: 12px;
  color: var(--color-muted);
}

.app-arrow {
  color: var(--color-muted);
  margin-left: 15px;
  transition: transform 0.2s, color 0.2s;
}

.application-card:hover .app-arrow {
  color: var(--color-accent);
  transform: translateX(4px);
}

.p-lg {
  padding: 40px;
}
</style>
