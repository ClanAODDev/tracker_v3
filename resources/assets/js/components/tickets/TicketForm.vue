<template>
  <div class="ticket-form">
    <div class="form-header">
      <div class="type-icon">
        <i :class="getIconForType(store.selectedType?.slug)"></i>
      </div>
      <div class="type-info">
        <span class="type-label">New Request</span>
        <h4 class="type-name">{{ store.selectedType?.name }}</h4>
      </div>
    </div>

    <div v-if="store.selectedType?.boilerplate" class="boilerplate-card">
      <div class="boilerplate-header">
        <i class="fa fa-lightbulb-o m-r-sm"></i>
        Please include the following information
      </div>
      <div class="boilerplate-content">
        <pre>{{ store.selectedType.boilerplate }}</pre>
      </div>
    </div>

    <div v-if="store.errors.submission" class="alert alert-danger">
      <i class="fa fa-exclamation-triangle m-r-sm"></i>
      {{ store.errors.submission }}
    </div>

    <form @submit.prevent="submitTicket">
      <div class="form-group">
        <label for="description" class="form-label">
          <i class="fa fa-pencil-square-o m-r-sm"></i>
          Description
        </label>
        <textarea
          id="description"
          v-model="store.newTicket.description"
          class="form-control"
          rows="10"
          :placeholder="store.selectedType?.boilerplate || 'Describe your issue or request in detail...'"
          :disabled="store.loading.submitting"
          required
          minlength="25"
        ></textarea>
        <div class="char-counter" :class="{ 'counter-valid': store.newTicket.description.length >= 25 }">
          <span class="counter-current">{{ store.newTicket.description.length }}</span>
          <span class="counter-divider">/</span>
          <span class="counter-min">25 minimum</span>
        </div>
      </div>

      <div class="form-actions">
        <button
          type="button"
          class="btn btn-default"
          @click="cancel"
          :disabled="store.loading.submitting"
        >
          <i class="fa fa-times m-r-xs"></i>
          Cancel
        </button>
        <button
          type="submit"
          class="btn btn-primary"
          :disabled="store.loading.submitting || store.newTicket.description.length < 25"
        >
          <span v-if="store.loading.submitting">
            <i class="fa fa-spinner fa-spin m-r-sm"></i>
            Creating Ticket...
          </span>
          <span v-else>
            <i class="fa fa-paper-plane m-r-sm"></i>
            Submit Request
          </span>
        </button>
      </div>
    </form>
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
    submitTicket() {
      store.submitTicket()
        .catch(() => {});
    },

    cancel() {
      store.resetForm();
      store.setView('select-type');
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
.ticket-form {
  padding: 10px 0;
}

.form-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 25px;
}

.type-icon {
  width: 56px;
  height: 56px;
  background: var(--color-accent);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.type-icon i {
  color: var(--color-white);
  font-size: 24px;
}

.type-info {
  display: flex;
  flex-direction: column;
}

.type-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--color-muted);
  font-weight: 500;
}

.type-name {
  font-size: 18px;
  font-weight: 600;
  color: var(--color-white);
  margin: 0;
}

.boilerplate-card {
  background: rgba(86, 192, 224, 0.1);
  border: 1px solid rgba(86, 192, 224, 0.2);
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 25px;
}

.boilerplate-header {
  background: rgba(86, 192, 224, 0.15);
  padding: 10px 15px;
  font-weight: 600;
  color: var(--color-info);
  font-size: 13px;
  border-bottom: 1px solid rgba(86, 192, 224, 0.2);
}

.boilerplate-content {
  padding: 15px;
}

.boilerplate-content pre {
  margin: 0;
  white-space: pre-wrap;
  font-family: inherit;
  font-size: 13px;
  color: var(--color-muted);
  line-height: 1.6;
}

.form-label {
  font-weight: 600;
  color: var(--color-white);
  font-size: 14px;
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.form-group {
  position: relative;
  margin-bottom: 20px;
}

.form-group textarea {
  background: var(--color-bg-input);
  border: 1px solid var(--overlay-light);
  color: var(--color-white);
  resize: vertical;
  min-height: 200px;
  transition: border-color 0.2s;
}

.form-group textarea:focus {
  background: var(--color-bg-input);
  border-color: var(--color-accent);
  color: var(--color-white);
  box-shadow: none;
}

.form-group textarea::placeholder {
  color: var(--color-muted);
}

.form-group textarea:disabled {
  background: var(--overlay-dark);
  cursor: not-allowed;
}

.char-counter {
  position: absolute;
  bottom: 10px;
  right: 10px;
  font-size: 11px;
  color: var(--color-muted);
  background: var(--color-bg-panel);
  padding: 4px 8px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.counter-current {
  font-weight: 600;
  color: var(--color-danger);
  transition: color 0.2s;
}

.counter-valid .counter-current {
  color: var(--color-success);
}

.counter-divider {
  color: var(--color-muted);
}

.counter-min {
  color: var(--color-muted);
}

.form-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 15px;
  border-top: 1px solid var(--overlay-light);
}
</style>
