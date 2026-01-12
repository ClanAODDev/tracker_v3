<template>
  <div class="ticket-detail">
    <div v-if="store.loading.ticket" class="text-center p-lg">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
      <p class="m-t-md text-muted">Loading ticket...</p>
    </div>

    <div v-else-if="store.errors.ticket" class="alert alert-danger">
      <i class="fa fa-exclamation-circle m-r-sm"></i>
      {{ store.errors.ticket }}
      <button class="btn btn-sm btn-outline-danger pull-right" @click="reload">
        <i class="fa fa-refresh"></i> Retry
      </button>
    </div>

    <div v-else-if="store.currentTicket">
      <div class="ticket-header" :class="'header-' + store.currentTicket.state">
        <div class="header-main">
          <div class="header-top">
            <span class="ticket-number">#{{ store.currentTicket.id }}</span>
            <span class="status-pill" :class="'pill-' + store.currentTicket.state">
              <i :class="getStatusIcon(store.currentTicket.state)"></i>
              {{ formatStatus(store.currentTicket.state) }}
            </span>
          </div>
          <div class="ticket-type">{{ store.currentTicket.type?.name ?? 'Unknown Type' }}</div>
        </div>
        <div class="header-meta">
          <div class="meta-block" v-if="store.currentTicket.owner">
            <div class="meta-label">Assigned To</div>
            <div class="meta-value">
              <i class="fa fa-user-circle m-r-xs"></i>
              {{ store.currentTicket.owner.name }}
            </div>
          </div>
          <div class="meta-block" v-else>
            <div class="meta-label">Status</div>
            <div class="meta-value text-muted">
              <i class="fa fa-hourglass-half m-r-xs"></i>
              Awaiting Assignment
            </div>
          </div>
          <div class="meta-block">
            <div class="meta-label">Created</div>
            <div class="meta-value">{{ store.formatDate(store.currentTicket.created_at) }}</div>
          </div>
        </div>
      </div>

      <div v-if="showAdminActions" class="admin-actions m-b-md">
        <div v-if="store.errors.action" class="alert alert-danger m-b-sm">
          <i class="fa fa-exclamation-circle m-r-xs"></i>
          {{ store.errors.action }}
        </div>
        <div class="action-buttons">
          <button
            v-if="canAssign"
            class="btn btn-info btn-sm"
            :disabled="store.loading.action"
            @click="assignToMe"
          >
            <i class="fa fa-hand-paper-o m-r-xs"></i> Assign to Me
          </button>
          <button
            v-if="canResolve"
            class="btn btn-success btn-sm"
            :disabled="store.loading.action"
            @click="resolveTicket"
          >
            <i class="fa fa-check m-r-xs"></i> Resolve
          </button>
          <button
            v-if="canReject"
            class="btn btn-danger btn-sm"
            :disabled="store.loading.action"
            @click="showRejectModal = true"
          >
            <i class="fa fa-times m-r-xs"></i> Reject
          </button>
          <button
            v-if="canReopen"
            class="btn btn-warning btn-sm"
            :disabled="store.loading.action"
            @click="reopenTicket"
          >
            <i class="fa fa-undo m-r-xs"></i> Reopen
          </button>
        </div>
      </div>

      <div v-if="showRejectModal" class="reject-modal-overlay" @click.self="showRejectModal = false">
        <div class="reject-modal">
          <div class="reject-modal-header">
            <h5>Reject Ticket</h5>
            <button type="button" class="close-btn" @click="showRejectModal = false">
              <i class="fa fa-times"></i>
            </button>
          </div>
          <div class="reject-modal-body">
            <div class="form-group">
              <label>Reason for rejection</label>
              <textarea
                v-model="rejectReason"
                class="form-control"
                rows="3"
                placeholder="Explain why this ticket is being rejected..."
              ></textarea>
            </div>
          </div>
          <div class="reject-modal-footer">
            <button class="btn btn-default btn-sm" @click="showRejectModal = false">Cancel</button>
            <button
              class="btn btn-danger btn-sm"
              :disabled="rejectReason.length < 5 || store.loading.action"
              @click="rejectTicket"
            >
              <span v-if="store.loading.action">
                <span class="themed-spinner spinner-sm"></span> Rejecting...
              </span>
              <span v-else>Reject Ticket</span>
            </button>
          </div>
        </div>
      </div>

      <div class="ticket-sections">
        <div class="section-card">
          <div class="section-header">
            <i class="fa fa-file-text-o m-r-sm"></i>
            Description
          </div>
          <div class="section-content">
            <p class="description-text" v-html="autolink(store.currentTicket.description)"></p>
          </div>
        </div>

        <div class="section-card">
          <div class="section-header">
            <i class="fa fa-comments-o m-r-sm"></i>
            Discussion
            <span class="comment-count">{{ userCommentCount }}</span>
            <button
              type="button"
              class="sound-toggle"
              :class="{ 'sound-off': !store.soundEnabled }"
              @click="store.toggleSound()"
              :title="store.soundEnabled ? 'Sound on - click to mute' : 'Sound off - click to unmute'"
            >
              <i :class="store.soundEnabled ? 'fa fa-volume-up' : 'fa fa-volume-off'"></i>
            </button>
          </div>
          <div class="section-content">
            <div v-if="!store.currentTicket.comments || store.currentTicket.comments.length === 0" class="text-center p-md">
              <i class="fa fa-comment-o fa-2x text-muted m-b-sm"></i>
              <p class="text-muted m-b-none">No comments yet</p>
            </div>

            <div v-else class="comments-timeline">
              <template v-for="comment in sortedComments" :key="comment.id">
                <div v-if="isSystemMessage(comment)" class="system-event">
                  <div class="event-line"></div>
                  <div class="event-content">
                    <i :class="getSystemIcon(comment.body)"></i>
                    <span class="event-text">
                      <strong>{{ comment.user?.name ?? 'Unknown' }}</strong> {{ formatSystemMessage(comment.body) }}
                    </span>
                    <span class="event-time">{{ store.formatRelativeDate(comment.created_at) }}</span>
                  </div>
                </div>

                <div v-else class="comment-item" :class="{ 'comment-admin': comment.user?.is_admin }">
                  <div class="comment-avatar">
                    <i class="fa fa-user"></i>
                  </div>
                  <div class="comment-bubble">
                    <div class="comment-header">
                      <span class="comment-author">
                        {{ comment.user?.name ?? 'Unknown' }}
                        <span v-if="comment.user?.is_admin" class="admin-badge">Admin</span>
                      </span>
                      <span class="comment-time">{{ store.formatRelativeDate(comment.created_at) }}</span>
                    </div>
                    <div class="comment-body" v-html="autolink(comment.body)"></div>
                  </div>
                </div>
              </template>
            </div>

            <div v-if="!isResolved" class="comment-form">
              <div v-if="store.errors.comment" class="alert alert-danger m-b-md">
                <i class="fa fa-exclamation-circle m-r-xs"></i>
                {{ store.errors.comment }}
              </div>
              <div class="form-group">
                <textarea
                  v-model="store.newComment"
                  class="form-control"
                  rows="3"
                  placeholder="Write a reply..."
                  :disabled="store.loading.commenting"
                ></textarea>
              </div>
              <div class="comment-actions">
                <small class="text-muted">Minimum 5 characters</small>
                <button
                  type="button"
                  class="btn btn-primary"
                  :disabled="store.loading.commenting || store.newComment.length < 5"
                  @click="submitComment"
                >
                  <span v-if="store.loading.commenting">
                    <span class="themed-spinner spinner-sm"></span> Sending...
                  </span>
                  <span v-else>
                    <i class="fa fa-paper-plane m-r-xs"></i> Send Reply
                  </span>
                </button>
              </div>
            </div>

            <div v-else class="closed-notice">
              <i class="fa fa-lock m-r-sm"></i>
              This ticket is {{ store.currentTicket.state }} and closed for further comments.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import store from './store.js';

const SYSTEM_PATTERNS = [
  'owned the ticket',
  'assigned the ticket to',
  'resolved the ticket',
  'reopened the ticket',
  'rejected the ticket',
];

export default {
  data() {
    return {
      store,
      showRejectModal: false,
      rejectReason: '',
    };
  },

  computed: {
    isResolved() {
      return store.currentTicket?.state === 'resolved' || store.currentTicket?.state === 'rejected';
    },

    sortedComments() {
      if (!store.currentTicket?.comments) return [];
      return [...store.currentTicket.comments].sort((a, b) =>
        new Date(a.created_at) - new Date(b.created_at)
      );
    },

    userCommentCount() {
      if (!store.currentTicket?.comments) return 0;
      return store.currentTicket.comments.filter(c => !this.isSystemMessage(c)).length;
    },

    showAdminActions() {
      return store.canWorkTickets && store.viewMode === 'admin';
    },

    currentUserId() {
      return store.getCurrentUserId();
    },

    canAssign() {
      const ticket = store.currentTicket;
      if (!ticket) return false;
      return ticket.state === 'new' || (ticket.state === 'assigned' && ticket.owner?.id !== this.currentUserId);
    },

    canResolve() {
      const ticket = store.currentTicket;
      if (!ticket) return false;
      return ticket.state === 'new' || ticket.state === 'assigned';
    },

    canReject() {
      const ticket = store.currentTicket;
      if (!ticket) return false;
      return ticket.state === 'new' || ticket.state === 'assigned';
    },

    canReopen() {
      const ticket = store.currentTicket;
      if (!ticket) return false;
      return ticket.state === 'resolved' || ticket.state === 'rejected';
    },
  },

  methods: {
    reload() {
      if (store.selectedTicketId) {
        store.loadTicket(store.selectedTicketId);
      }
    },

    submitComment() {
      store.submitComment()
        .catch(() => {});
    },

    assignToMe() {
      store.ownTicket(store.currentTicket.id)
        .catch(() => {});
    },

    resolveTicket() {
      store.resolveTicket(store.currentTicket.id)
        .catch(() => {});
    },

    rejectTicket() {
      store.rejectTicket(store.currentTicket.id, this.rejectReason)
        .then(() => {
          this.showRejectModal = false;
          this.rejectReason = '';
        })
        .catch(() => {});
    },

    reopenTicket() {
      store.reopenTicket(store.currentTicket.id)
        .catch(() => {});
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

    isSystemMessage(comment) {
      const body = comment.body.toLowerCase();
      return SYSTEM_PATTERNS.some(pattern => body.includes(pattern));
    },

    getSystemIcon(body) {
      const lower = body.toLowerCase();
      if (lower.includes('resolved')) return 'fa fa-check-circle text-success';
      if (lower.includes('rejected')) return 'fa fa-times-circle text-danger';
      if (lower.includes('reopened')) return 'fa fa-undo text-warning';
      if (lower.includes('owned') || lower.includes('assigned')) return 'fa fa-user-plus text-info';
      return 'fa fa-info-circle text-muted';
    },

    formatSystemMessage(body) {
      if (body.toLowerCase() === 'owned the ticket') {
        return 'took ownership of this ticket';
      }
      if (body.toLowerCase() === 'resolved the ticket') {
        return 'marked this ticket as resolved';
      }
      if (body.toLowerCase() === 'rejected the ticket') {
        return 'closed this ticket';
      }
      if (body.toLowerCase() === 'reopened the ticket') {
        return 'reopened this ticket';
      }
      if (body.toLowerCase().includes('assigned the ticket to')) {
        return body.replace(/^.*?assigned the ticket to /i, 'assigned this ticket to ');
      }
      return body;
    },

    autolink(text) {
      if (!text) return '';
      const escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
      const urlRegex = /(https?:\/\/[^\s<]+)/g;
      return escaped.replace(urlRegex, '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>');
    },
  },
};
</script>

<style scoped>
.ticket-detail {
  padding: 10px 0;
}

.ticket-header {
  background: var(--overlay-dark);
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 15px;
  border-left: 4px solid var(--color-info);
}

.header-new { border-left-color: var(--color-info); }
.header-assigned { border-left-color: var(--color-accent); }
.header-resolved { border-left-color: var(--color-success); }
.header-rejected { border-left-color: var(--color-danger); }

.header-top {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.ticket-number {
  font-size: 20px;
  font-weight: 700;
  color: var(--color-white);
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: var(--overlay-light);
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
  color: var(--color-white);
}

.pill-new { background: rgba(86, 192, 224, 0.3); color: var(--color-info); }
.pill-assigned { background: rgba(246, 168, 33, 0.3); color: var(--color-accent); }
.pill-resolved { background: rgba(27, 191, 137, 0.3); color: var(--color-success); }
.pill-rejected { background: rgba(219, 82, 75, 0.3); color: var(--color-danger); }

.ticket-type {
  font-size: 14px;
  color: var(--color-muted);
}

.header-meta {
  display: flex;
  gap: 30px;
  flex-wrap: wrap;
}

.meta-block {
  text-align: right;
}

.meta-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--color-muted);
  margin-bottom: 4px;
}

.meta-value {
  font-size: 14px;
  font-weight: 500;
  color: var(--color-white);
}

.ticket-sections {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.section-card {
  background: var(--overlay-dark);
  border-radius: 8px;
  overflow: hidden;
}

.section-header {
  background: rgba(0, 0, 0, 0.2);
  padding: 12px 20px;
  font-weight: 600;
  color: var(--color-white);
  display: flex;
  align-items: center;
}

.comment-count {
  background: var(--overlay-light);
  color: var(--color-muted);
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 10px;
  margin-left: 8px;
  font-weight: 500;
}

.sound-toggle {
  margin-left: auto;
  background: var(--overlay-light);
  border: none;
  color: var(--color-success);
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.sound-toggle:hover {
  background: var(--overlay-light-hover);
}

.sound-toggle.sound-off {
  color: var(--color-muted);
}

.section-content {
  padding: 20px;
}

.description-text {
  white-space: pre-wrap;
  color: var(--color-muted);
  line-height: 1.6;
  margin-bottom: 0;
}

.comments-timeline {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.system-event {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 0;
}

.event-line {
  flex: 0 0 36px;
  height: 1px;
  background: var(--overlay-light);
}

.event-content {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.event-content i {
  font-size: 14px;
}

.event-text {
  font-size: 12px;
  color: var(--color-muted);
}

.event-text strong {
  color: var(--color-gray-300);
  font-weight: 500;
}

.event-time {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.3);
  margin-left: auto;
}

.comment-item {
  display: flex;
  gap: 12px;
}

.comment-avatar {
  width: 36px;
  height: 36px;
  background: var(--overlay-light);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  color: var(--color-muted);
}

.comment-admin .comment-avatar {
  background: color-mix(in srgb, var(--color-accent) 30%, transparent);
  color: var(--color-accent);
}

.comment-bubble {
  flex: 1;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 8px;
  padding: 12px 15px;
  min-width: 0;
}

.comment-admin .comment-bubble {
  background: color-mix(in srgb, var(--color-accent) 10%, transparent);
  border: 1px solid color-mix(in srgb, var(--color-accent) 20%, transparent);
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  flex-wrap: wrap;
  gap: 8px;
}

.comment-author {
  font-weight: 600;
  color: var(--color-white);
  font-size: 13px;
}

.admin-badge {
  background: var(--color-accent);
  color: var(--color-white);
  font-size: 9px;
  padding: 2px 6px;
  border-radius: 3px;
  margin-left: 6px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 700;
}

.comment-time {
  font-size: 11px;
  color: var(--color-muted);
}

.comment-body {
  color: var(--color-muted);
  font-size: 13px;
  line-height: 1.5;
  white-space: pre-wrap;
}

.comment-body :deep(a),
.description-text :deep(a) {
  color: var(--color-accent);
  text-decoration: none;
  word-break: break-all;
}

.comment-body :deep(a):hover,
.description-text :deep(a):hover {
  text-decoration: underline;
}

.comment-form {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid var(--overlay-light);
}

.comment-form textarea {
  background: var(--color-bg-input);
  border: 1px solid var(--overlay-light);
  color: var(--color-white);
  resize: none;
}

.comment-form textarea:focus {
  background: var(--color-bg-input);
  border-color: var(--color-accent);
  color: var(--color-white);
  box-shadow: none;
}

.comment-form textarea::placeholder {
  color: var(--color-muted);
}

.comment-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 10px;
}

.comment-actions .btn {
  margin-left: auto;
}

.closed-notice {
  background: rgba(0, 0, 0, 0.2);
  color: var(--color-muted);
  padding: 15px;
  border-radius: 6px;
  text-align: center;
  margin-top: 15px;
}

.p-lg {
  padding: 40px;
}

.p-md {
  padding: 30px;
}

.admin-actions {
  background: var(--overlay-dark);
  border-radius: 8px;
  padding: 15px;
}

.action-buttons {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.reject-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1060;
}

.reject-modal {
  background: var(--color-bg-secondary);
  border-radius: 8px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.reject-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  border-bottom: 1px solid var(--overlay-light);
}

.reject-modal-header h5 {
  margin: 0;
  color: var(--color-white);
}

.close-btn {
  background: transparent;
  border: none;
  color: var(--color-muted);
  cursor: pointer;
  font-size: 18px;
}

.close-btn:hover {
  color: var(--color-white);
}

.reject-modal-body {
  padding: 20px;
}

.reject-modal-body textarea {
  background: var(--color-bg-input);
  border: 1px solid var(--overlay-light);
  color: var(--color-white);
  resize: none;
}

.reject-modal-body textarea:focus {
  background: var(--color-bg-input);
  border-color: var(--color-accent);
  color: var(--color-white);
  box-shadow: none;
}

.reject-modal-body textarea::placeholder {
  color: var(--color-muted);
}

.reject-modal-body label {
  color: var(--color-muted);
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
}

.reject-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 15px 20px;
  border-top: 1px solid var(--overlay-light);
}
</style>
