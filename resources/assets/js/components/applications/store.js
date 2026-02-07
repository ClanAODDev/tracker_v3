import { reactive } from 'vue';

const store = reactive({
    baseUrl: '',
    canDelete: false,
    currentView: 'list',
    currentApplication: null,

    loading: {
        applications: false,
        application: false,
        commenting: false,
        deleting: false,
        deletingComment: null,
    },

    errors: {
        applications: null,
        application: null,
        comment: null,
    },

    applications: [],
    newComment: '',
});

store.init = (baseUrl, canDelete) => {
    store.baseUrl = baseUrl;
    store.canDelete = canDelete;
};

store.loadApplications = () => {
    store.loading.applications = true;
    store.errors.applications = null;

    return axios.get(store.baseUrl)
        .then((response) => {
            store.applications = response.data.applications || [];
            store.loading.applications = false;
        })
        .catch(() => {
            store.loading.applications = false;
            store.errors.applications = 'Failed to load applications. Please try again.';
        });
};

store.loadApplication = (id) => {
    store.loading.application = true;
    store.errors.application = null;

    return axios.get(`${store.baseUrl}/${id}`)
        .then((response) => {
            store.currentApplication = response.data.application;
            store.loading.application = false;
        })
        .catch(() => {
            store.loading.application = false;
            store.errors.application = 'Failed to load application. Please try again.';
        });
};

store.deleteApplication = (id) => {
    if (store.loading.deleting) return Promise.reject('Already deleting');

    store.loading.deleting = true;

    return axios.delete(`${store.baseUrl}/${id}`)
        .then(() => {
            store.applications = store.applications.filter(a => a.id !== id);
            store.loading.deleting = false;
            store.setView('list');
        })
        .catch((error) => {
            store.loading.deleting = false;
            throw error;
        });
};

store.submitComment = () => {
    if (store.loading.commenting || !store.currentApplication) {
        return Promise.reject('Cannot submit comment');
    }

    if (!store.newComment || store.newComment.length < 5) {
        store.errors.comment = 'Comment must be at least 5 characters.';
        return Promise.reject('Validation failed');
    }

    store.loading.commenting = true;
    store.errors.comment = null;

    return axios.post(`${store.baseUrl}/${store.currentApplication.id}/comments`, {
        body: store.newComment,
    })
        .then((response) => {
            store.loading.commenting = false;
            const comments = store.currentApplication.comments || [];
            comments.push(response.data.comment);
            store.currentApplication.comments = [...comments];
            store.newComment = '';

            const app = store.applications.find(a => a.id === store.currentApplication.id);
            if (app) {
                app.comments_count = (app.comments_count || 0) + 1;
            }
        })
        .catch((error) => {
            store.loading.commenting = false;
            store.errors.comment = error.response?.data?.message || 'Failed to add comment. Please try again.';
            throw error;
        });
};

store.deleteComment = (commentId) => {
    if (store.loading.deletingComment) return Promise.reject('Already deleting');

    store.loading.deletingComment = commentId;

    return axios.delete(`${store.baseUrl}/${store.currentApplication.id}/comments/${commentId}`)
        .then(() => {
            store.currentApplication.comments = store.currentApplication.comments.filter(c => c.id !== commentId);
            store.loading.deletingComment = null;

            const app = store.applications.find(a => a.id === store.currentApplication.id);
            if (app) {
                app.comments_count = Math.max(0, (app.comments_count || 1) - 1);
            }
        })
        .catch((error) => {
            store.loading.deletingComment = null;
            throw error;
        });
};

store.setView = (view, data = null) => {
    store.currentView = view;
    if (view === 'detail' && data) {
        store.loadApplication(data);
    } else if (view === 'list') {
        store.currentApplication = null;
        store.newComment = '';
        store.errors.comment = null;
    }
};

store.formatRelativeDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

store.getCurrentUserId = () => {
    return window.Laravel?.userId || null;
};

export default store;
