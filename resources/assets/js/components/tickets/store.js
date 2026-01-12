import { reactive } from 'vue';

const store = reactive({
    base_url: window.Laravel.appPath,

    currentView: 'list',
    selectedTicketId: null,
    pollInterval: null,
    soundEnabled: true,

    canWorkTickets: window.Laravel?.canWorkTickets || false,
    viewMode: 'user',
    allTickets: [],
    assignedTickets: [],
    filters: {
        state: null,
        search: '',
    },

    loading: {
        tickets: false,
        ticketTypes: false,
        ticket: false,
        submitting: false,
        commenting: false,
        allTickets: false,
        action: false,
    },

    errors: {
        tickets: null,
        ticketTypes: null,
        ticket: null,
        submission: null,
        comment: null,
        allTickets: null,
        action: null,
    },

    tickets: [],
    ticketTypes: [],
    selectedType: null,
    currentTicket: null,

    newTicket: {
        ticket_type_id: null,
        description: '',
    },

    newComment: '',
});

store.loadTickets = () => {
    store.loading.tickets = true;
    store.errors.tickets = null;

    return axios.get(`${store.base_url}/api/tickets`)
        .then((response) => {
            store.tickets = response.data.tickets || [];
            store.loading.tickets = false;
        })
        .catch((error) => {
            store.loading.tickets = false;
            store.errors.tickets = 'Failed to load tickets. Please try again.';
            console.error('Tickets load error:', error);
        });
};

store.loadTicketTypes = () => {
    store.loading.ticketTypes = true;
    store.errors.ticketTypes = null;

    return axios.get(`${store.base_url}/api/tickets/types`)
        .then((response) => {
            store.ticketTypes = response.data.types || [];
            store.loading.ticketTypes = false;
        })
        .catch((error) => {
            store.loading.ticketTypes = false;
            store.errors.ticketTypes = 'Failed to load ticket types. Please try again.';
            console.error('Ticket types load error:', error);
        });
};

store.loadTicket = (ticketId) => {
    store.loading.ticket = true;
    store.errors.ticket = null;

    return axios.get(`${store.base_url}/api/tickets/${ticketId}`)
        .then((response) => {
            store.currentTicket = response.data.ticket;
            store.loading.ticket = false;
        })
        .catch((error) => {
            store.loading.ticket = false;
            store.errors.ticket = 'Failed to load ticket. Please try again.';
            console.error('Ticket load error:', error);
        });
};

store.submitTicket = () => {
    if (store.loading.submitting) {
        return Promise.reject('Already submitting');
    }

    if (!store.newTicket.ticket_type_id || !store.newTicket.description || store.newTicket.description.length < 25) {
        store.errors.submission = 'Please fill in all required fields (description must be at least 25 characters).';
        return Promise.reject('Validation failed');
    }

    store.loading.submitting = true;
    store.errors.submission = null;

    return axios.post(`${store.base_url}/api/tickets`, {
        ticket_type_id: store.newTicket.ticket_type_id,
        description: store.newTicket.description,
    })
        .then((response) => {
            store.loading.submitting = false;
            store.tickets.unshift(response.data.ticket);
            store.currentTicket = response.data.ticket;
            store.resetForm();
            store.setView('detail', response.data.ticket.id);
        })
        .catch((error) => {
            store.loading.submitting = false;
            store.errors.submission = error.response?.data?.message || 'Failed to create ticket. Please try again.';
            throw error;
        });
};

store.submitComment = () => {
    if (store.loading.commenting || !store.currentTicket) {
        return Promise.reject('Cannot submit comment');
    }

    if (!store.newComment || store.newComment.length < 5) {
        store.errors.comment = 'Comment must be at least 5 characters.';
        return Promise.reject('Validation failed');
    }

    store.loading.commenting = true;
    store.errors.comment = null;

    return axios.post(`${store.base_url}/api/tickets/${store.currentTicket.id}/comments`, {
        body: store.newComment,
    })
        .then((response) => {
            store.loading.commenting = false;
            const comments = store.currentTicket.comments || [];
            comments.push(response.data.comment);
            store.currentTicket.comments = [...comments];
            store.newComment = '';
        })
        .catch((error) => {
            store.loading.commenting = false;
            store.errors.comment = error.response?.data?.message || 'Failed to add comment. Please try again.';
            throw error;
        });
};

store.setView = (view, data = null, updateUrl = true) => {
    store.stopPolling();
    store.currentView = view;
    if (view === 'detail' && data) {
        store.selectedTicketId = data;
        store.loadTicket(data);
        store.startPolling();
        if (updateUrl) {
            window.history.pushState({ view: 'detail', ticketId: data }, '', `${store.base_url}/help/tickets/${data}`);
        }
    } else if (view === 'create' && data) {
        store.selectType(data);
    } else if (view === 'list') {
        store.currentTicket = null;
        store.selectedTicketId = null;
        if (updateUrl) {
            const url = new URL(store.base_url + '/help/tickets', window.location.origin);
            if (store.viewMode !== 'user') {
                url.searchParams.set('view', store.viewMode);
            }
            window.history.pushState({ view: 'list', viewMode: store.viewMode }, '', url);
        }
    }
};

store.startPolling = () => {
    store.stopPolling();
    store.pollInterval = setInterval(() => {
        if (store.selectedTicketId && !store.loading.ticket) {
            store.refreshTicket();
        }
    }, 30000);
};

store.stopPolling = () => {
    if (store.pollInterval) {
        clearInterval(store.pollInterval);
        store.pollInterval = null;
    }
};

store.refreshTicket = () => {
    if (!store.selectedTicketId) return;

    const previousCommentCount = store.currentTicket?.comments?.length || 0;

    axios.get(`${store.base_url}/api/tickets/${store.selectedTicketId}`)
        .then((response) => {
            if (store.currentTicket && response.data.ticket) {
                const newCommentCount = response.data.ticket.comments?.length || 0;

                if (newCommentCount > previousCommentCount) {
                    store.playNotificationSound();
                }

                store.currentTicket.comments = response.data.ticket.comments;
                store.currentTicket.state = response.data.ticket.state;
                store.currentTicket.owner = response.data.ticket.owner;
            }
        })
        .catch(() => {});
};

store.playNotificationSound = () => {
    if (!store.soundEnabled) return;

    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
        oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);

        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.3);
    } catch (e) {
        console.log('Could not play notification sound');
    }
};

store.toggleSound = () => {
    store.soundEnabled = !store.soundEnabled;
};

store.selectType = (type) => {
    store.selectedType = type;
    store.newTicket.ticket_type_id = type.id;
    store.newTicket.description = '';
};

store.resetForm = () => {
    store.newTicket.ticket_type_id = null;
    store.newTicket.description = '';
    store.selectedType = null;
    store.errors.submission = null;
};

store.getStateColor = (state) => {
    const colors = {
        new: 'info',
        assigned: 'warning',
        resolved: 'success',
        rejected: 'danger',
    };
    return colors[state] || 'secondary';
};

store.getStateBadgeClass = (state) => {
    return `badge badge-${store.getStateColor(state)}`;
};

store.formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
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
    return store.formatDate(dateString);
};

store.setViewMode = (mode, updateUrl = true) => {
    store.viewMode = mode;
    if (mode === 'all' || mode === 'assigned') {
        store.loadAllTickets();
    } else {
        store.loadTickets();
    }
    store.setView('list');

    if (updateUrl) {
        const url = new URL(window.location);
        if (mode === 'user') {
            url.searchParams.delete('view');
        } else {
            url.searchParams.set('view', mode);
        }
        window.history.replaceState({}, '', url);
    }
};

store.initFromUrl = () => {
    const params = new URLSearchParams(window.location.search);
    const view = params.get('view');
    if (view && ['assigned', 'all'].includes(view) && store.canWorkTickets) {
        store.setViewMode(view, false);
    }
};

store.initPopstateHandler = () => {
    window.addEventListener('popstate', (event) => {
        if (event.state?.view === 'detail' && event.state?.ticketId) {
            store.setView('detail', event.state.ticketId, false);
        } else if (event.state?.view === 'list') {
            if (event.state.viewMode && store.canWorkTickets) {
                store.viewMode = event.state.viewMode;
            }
            store.setView('list', null, false);
        } else {
            store.setView('list', null, false);
        }
    });
};

store.loadAllTickets = () => {
    store.loading.allTickets = true;
    store.errors.allTickets = null;

    return axios.get(`${store.base_url}/api/tickets/workable`)
        .then((response) => {
            const tickets = response.data.tickets || [];
            store.allTickets = tickets;
            store.assignedTickets = tickets.filter(t => t.owner?.id === store.getCurrentUserId());
            store.loading.allTickets = false;
        })
        .catch((error) => {
            store.loading.allTickets = false;
            store.errors.allTickets = 'Failed to load tickets. Please try again.';
            console.error('All tickets load error:', error);
        });
};

store.getFilteredTickets = () => {
    let tickets = store.viewMode === 'assigned' ? store.assignedTickets : store.allTickets;

    if (store.filters.state) {
        tickets = tickets.filter(t => t.state === store.filters.state);
    }

    if (store.filters.search) {
        const search = store.filters.search.toLowerCase();
        tickets = tickets.filter(t =>
            t.description?.toLowerCase().includes(search) ||
            t.caller?.name?.toLowerCase().includes(search) ||
            t.type?.name?.toLowerCase().includes(search)
        );
    }

    return tickets;
};

store.performAction = (ticketId, action, data = {}, errorMessage = 'Action failed.') => {
    if (store.loading.action) return Promise.reject('Action in progress');

    store.loading.action = true;
    store.errors.action = null;

    return axios.post(`${store.base_url}/api/tickets/${ticketId}/${action}`, data)
        .then((response) => {
            store.loading.action = false;
            store.updateTicketInLists(response.data.ticket);
            if (store.currentTicket?.id === ticketId) {
                store.currentTicket = response.data.ticket;
            }
            return response.data;
        })
        .catch((error) => {
            store.loading.action = false;
            store.errors.action = error.response?.data?.message || errorMessage;
            throw error;
        });
};

store.ownTicket = (ticketId) => store.performAction(ticketId, 'own', {}, 'Failed to assign ticket.');
store.resolveTicket = (ticketId) => store.performAction(ticketId, 'resolve', {}, 'Failed to resolve ticket.');
store.rejectTicket = (ticketId, reason) => store.performAction(ticketId, 'reject', { reason }, 'Failed to reject ticket.');
store.reopenTicket = (ticketId) => store.performAction(ticketId, 'reopen', {}, 'Failed to reopen ticket.');

store.updateTicketInLists = (ticket) => {
    const userIndex = store.tickets.findIndex(t => t.id === ticket.id);
    if (userIndex !== -1) {
        store.tickets[userIndex] = ticket;
    }

    const allIndex = store.allTickets.findIndex(t => t.id === ticket.id);
    if (allIndex !== -1) {
        store.allTickets[allIndex] = ticket;
    }

    const currentUserId = store.getCurrentUserId();
    if (ticket.owner?.id === currentUserId) {
        const assignedIndex = store.assignedTickets.findIndex(t => t.id === ticket.id);
        if (assignedIndex === -1) {
            store.assignedTickets.push(ticket);
        } else {
            store.assignedTickets[assignedIndex] = ticket;
        }
    } else {
        store.assignedTickets = store.assignedTickets.filter(t => t.id !== ticket.id);
    }
};

store.getCurrentUserId = () => {
    return window.Laravel?.userId || null;
};

export default store;
