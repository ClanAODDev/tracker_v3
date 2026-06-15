<template>
  <div class="application-detail">
    <div v-if="store.loading.application" class="text-center p-lg">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
      <p class="m-t-md text-muted">Loading application...</p>
    </div>

    <div v-else-if="store.errors.application" class="alert alert-danger">
      <i class="fa fa-exclamation-circle m-r-sm"></i>
      {{ store.errors.application }}
      <button class="btn btn-sm btn-outline-danger pull-right" @click="reload">
        <i class="fa fa-refresh"></i> Retry
      </button>
    </div>

    <div v-else-if="store.currentApplication">
      <div class="app-header">
        <div class="header-identity">
          <img v-if="store.currentApplication.avatar" :src="store.currentApplication.avatar" :alt="store.currentApplication.discord_username" class="header-avatar">
          <div v-else class="header-avatar-placeholder"><i class="fab fa-discord"></i></div>
          <div class="header-main">
            <div class="app-username">{{ store.currentApplication.discord_username }}</div>
            <div class="app-meta">
              <span class="app-date">
                <i class="fa fa-clock-o m-r-xs"></i>{{ store.formatRelativeDate(store.currentApplication.created_at) }}
              </span>
              <template v-if="store.currentApplication.discord_id">
                <span class="app-meta-divider">·</span>
                <a
                  :href="`https://discord.com/users/${store.currentApplication.discord_id}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="discord-profile-link"
                  title="Open Discord profile"
                >
                  <i class="fab fa-discord m-r-xs"></i>Add Friend
                </a>
                <span class="app-meta-hint">(open in browser)</span>
              </template>
            </div>
          </div>
        </div>
        <button
          v-if="store.canDelete"
          class="btn btn-danger btn-sm"
          :disabled="store.loading.deleting"
          @click="confirmDelete"
        >
          <span v-if="store.loading.deleting">
            <span class="themed-spinner spinner-sm"></span> Deleting...
          </span>
          <span v-else>
            <i class="fa fa-trash m-r-xs"></i> Delete
          </span>
        </button>
      </div>

      <div class="detail-sections">
        <div class="section-card">
          <div class="section-header">
            <i class="fa fa-file-text-o m-r-sm"></i>
            Responses
          </div>
          <div class="section-content">
            <div
              v-for="(response, index) in store.currentApplication.responses"
              :key="index"
              class="response-item"
            >
              <div class="response-label" v-html="response.label"></div>
              <div class="response-value" v-html="autolink(response.value)"></div>
            </div>
          </div>
        </div>

        <div class="section-card">
          <div class="section-header">
            <i class="fas fa-comments m-r-sm"></i>
            Internal Comments (not visible to member)
            <span class="comment-count">{{ commentCount }}</span>
          </div>
          <div class="section-content">
            <div v-if="!store.currentApplication.comments || store.currentApplication.comments.length === 0" class="text-center p-md">
              <i class="fas fa-comment fa-2x text-muted m-b-sm"></i>
              <p class="text-muted m-b-none">No comments yet</p>
            </div>

            <div v-else class="comments-timeline">
              <div
                v-for="comment in sortedComments"
                :key="comment.id"
                class="comment-item"
              >
                <div class="comment-avatar">
                  <img v-if="comment.user?.avatar" :src="comment.user.avatar" :alt="comment.user?.name" class="avatar-img" />
                  <i v-else class="fa fa-user"></i>
                </div>
                <div class="comment-bubble">
                  <div class="comment-header">
                    <span class="comment-author">{{ comment.user?.name ?? 'Unknown' }}</span>
                    <div class="comment-header-right">
                      <span class="comment-time">{{ store.formatRelativeDate(comment.created_at) }}</span>
                      <button
                        v-if="comment.user?.id === currentUserId"
                        class="delete-comment-btn"
                        :disabled="store.loading.deletingComment === comment.id"
                        @click="deleteComment(comment.id)"
                        title="Delete comment"
                      >
                        <i class="fa fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <div class="comment-body" v-html="autolink(comment.body)"></div>
                </div>
              </div>
            </div>

            <div class="comment-form">
              <div v-if="store.errors.comment" class="alert alert-danger m-b-md">
                <i class="fa fa-exclamation-circle m-r-xs"></i>
                {{ store.errors.comment }}
              </div>
              <div class="form-group">
                <textarea
                  v-model="store.newComment"
                  class="form-control"
                  rows="3"
                  placeholder="Write a comment..."
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
                    <i class="fa fa-paper-plane m-r-xs"></i> Send
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import store from './store.js';

export default {
  data() {
    return { store };
  },

  computed: {
    sortedComments() {
      if (!store.currentApplication?.comments) return [];
      return [...store.currentApplication.comments].sort((a, b) =>
        new Date(a.created_at) - new Date(b.created_at)
      );
    },

    commentCount() {
      return store.currentApplication?.comments?.length || 0;
    },

    currentUserId() {
      return store.getCurrentUserId();
    },
  },

  methods: {
    reload() {
      if (store.currentApplication?.id) {
        store.loadApplication(store.currentApplication.id);
      }
    },

    submitComment() {
      store.submitComment().catch(() => {});
    },

    confirmDelete() {
      if (confirm('Are you sure you want to delete this application?')) {
        store.deleteApplication(store.currentApplication.id).catch(() => {});
      }
    },

    deleteComment(commentId) {
      store.deleteComment(commentId).catch(() => {});
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
.application-detail {
  padding: 10px 0;
}

.app-header {
  background: var(--overlay-dark);
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-left: 4px solid #5865F2;
}

.header-identity {
  display: flex;
  align-items: center;
  gap: 14px;
}

.header-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.header-avatar-placeholder {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--overlay-light);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-muted);
  font-size: 20px;
  flex-shrink: 0;
}

.app-username {
  font-size: 18px;
  font-weight: 700;
  color: var(--color-white);
  margin-bottom: 4px;
}

.app-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.app-date {
  font-size: 13px;
  color: var(--color-muted);
}

.app-meta-divider {
  color: var(--color-muted);
  font-size: 13px;
}

.app-meta-hint {
  font-size: 11px;
  color: var(--color-muted);
}

.discord-profile-link {
  font-size: 12px;
  color: #5865F2;
  text-decoration: none;
  font-weight: 600;
  background: rgba(88, 101, 242, 0.12);
  padding: 2px 8px;
  border-radius: 10px;
  transition: background 0.15s;
}

.discord-profile-link:hover {
  background: rgba(88, 101, 242, 0.25);
  color: #5865F2;
  text-decoration: none;
}

.detail-sections {
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

.section-content {
  padding: 20px;
}

.response-item {
  margin-bottom: 14px;
}

.response-item:last-child {
  margin-bottom: 0;
}

.response-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 600;
  color: var(--color-muted);
}

.response-value {
  margin-top: 2px;
  color: var(--color-white);
  line-height: 1.5;
  white-space: pre-wrap;
}

.response-value :deep(a) {
  color: var(--color-accent);
  text-decoration: none;
  word-break: break-all;
}

.response-value :deep(a):hover {
  text-decoration: underline;
}

.comments-timeline {
  display: flex;
  flex-direction: column;
  gap: 15px;
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
  overflow: hidden;
}

.avatar-img {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
}

.comment-bubble {
  flex: 1;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 8px;
  padding: 12px 15px;
  min-width: 0;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  flex-wrap: wrap;
  gap: 8px;
}

.comment-header-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

.comment-author {
  font-weight: 600;
  color: var(--color-white);
  font-size: 13px;
}

.comment-time {
  font-size: 11px;
  color: var(--color-muted);
}

.delete-comment-btn {
  background: transparent;
  border: none;
  color: var(--color-muted);
  cursor: pointer;
  padding: 2px 4px;
  font-size: 11px;
  opacity: 0.5;
  transition: opacity 0.2s, color 0.2s;
}

.delete-comment-btn:hover {
  opacity: 1;
  color: var(--color-danger);
}

.comment-body {
  color: var(--color-muted);
  font-size: 13px;
  line-height: 1.5;
  white-space: pre-wrap;
}

.comment-body :deep(a) {
  color: var(--color-accent);
  text-decoration: none;
  word-break: break-all;
}

.comment-body :deep(a):hover {
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

.p-lg {
  padding: 40px;
}

.p-md {
  padding: 30px;
}

.alert-danger {
  background: repeating-linear-gradient(
    -45deg,
    transparent,
    transparent 10px,
    color-mix(in srgb, var(--color-danger) 10%, transparent) 10px,
    color-mix(in srgb, var(--color-danger) 10%, transparent) 11px
  );
  border: 1px solid color-mix(in srgb, var(--color-danger) 35%, transparent);
  color: var(--color-text);
}
</style>
