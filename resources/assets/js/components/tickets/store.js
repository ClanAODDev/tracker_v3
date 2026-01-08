import { reactive } from 'vue';

const LAST_SEEN_KEY = 'ticket_last_seen';

const store = reactive({
    base_url: window.Laravel.appPath,

    currentView: 'list',
    selectedTicketId: null,
    pollInterval: null,
    unreadPollInterval: null,
    soundEnabled: true,
    unreadCount: 0,

    isAdmin: window.Laravel?.isAdmin || false,
    viewMode: 'user',
    adminTickets: [],
    adminFilters: {
        state: null,
        search: '',
    },

    loading: {
        tickets: false,
        ticketTypes: false,
        ticket: false,
        submitting: false,
        commenting: false,
        adminTickets: false,
        action: false,
    },

    errors: {
        tickets: null,
        ticketTypes: null,
        ticket: null,
        submission: null,
        comment: null,
        adminTickets: null,
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

store.setView = (view, data = null) => {
    store.stopPolling();
    store.currentView = view;
    if (view === 'detail' && data) {
        store.selectedTicketId = data;
        store.loadTicket(data);
        store.startPolling();
    } else if (view === 'create' && data) {
        store.selectType(data);
    } else if (view === 'list') {
        store.currentTicket = null;
        store.selectedTicketId = null;
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

store.getLastSeen = () => {
    try {
        const data = localStorage.getItem(LAST_SEEN_KEY);
        return data ? JSON.parse(data) : {};
    } catch {
        return {};
    }
};

store.setLastSeen = (ticketId) => {
    const lastSeen = store.getLastSeen();
    lastSeen[ticketId] = new Date().toISOString();
    try {
        localStorage.setItem(LAST_SEEN_KEY, JSON.stringify(lastSeen));
    } catch {}
};

store.markAllSeen = () => {
    const lastSeen = store.getLastSeen();
    store.tickets.forEach(ticket => {
        lastSeen[ticket.id] = new Date().toISOString();
    });
    try {
        localStorage.setItem(LAST_SEEN_KEY, JSON.stringify(lastSeen));
    } catch {}
    store.unreadCount = 0;
    store.updateBadge();
};

store.calculateUnreadCount = () => {
    const lastSeen = store.getLastSeen();
    let count = 0;

    store.tickets.forEach(ticket => {
        if (ticket.state === 'resolved' || ticket.state === 'rejected') {
            return;
        }

        const ticketLastSeen = lastSeen[ticket.id];
        if (!ticketLastSeen) {
            count++;
        } else {
            const seenDate = new Date(ticketLastSeen);
            const updatedDate = new Date(ticket.updated_at);
            if (updatedDate > seenDate) {
                count++;
            }
        }
    });

    store.unreadCount = count;
    store.updateBadge();
};

store.updateBadge = () => {
    const badges = document.querySelectorAll('.help-badge');
    badges.forEach(badge => {
        if (store.unreadCount > 0) {
            badge.textContent = store.unreadCount > 9 ? '9+' : store.unreadCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    });
};

store.startUnreadPolling = () => {
    store.checkUnread();
    store.unreadPollInterval = setInterval(() => {
        store.checkUnread();
    }, 60000);
};

store.stopUnreadPolling = () => {
    if (store.unreadPollInterval) {
        clearInterval(store.unreadPollInterval);
        store.unreadPollInterval = null;
    }
};

store.checkUnread = () => {
    axios.get(`${store.base_url}/api/tickets`)
        .then((response) => {
            store.tickets = response.data.tickets || [];
            store.calculateUnreadCount();
        })
        .catch(() => {});
};

store.setViewMode = (mode) => {
    store.viewMode = mode;
    if (mode === 'admin') {
        store.loadAdminTickets();
    } else {
        store.loadTickets();
    }
    store.setView('list');
};

store.loadAdminTickets = () => {
    store.loading.adminTickets = true;
    store.errors.adminTickets = null;

    return axios.get(`${store.base_url}/api/tickets/admin`)
        .then((response) => {
            store.adminTickets = response.data.tickets || [];
            store.loading.adminTickets = false;
        })
        .catch((error) => {
            store.loading.adminTickets = false;
            store.errors.adminTickets = 'Failed to load admin tickets. Please try again.';
            console.error('Admin tickets load error:', error);
        });
};

store.getFilteredAdminTickets = () => {
    let tickets = store.adminTickets;

    if (store.adminFilters.state) {
        tickets = tickets.filter(t => t.state === store.adminFilters.state);
    }

    if (store.adminFilters.search) {
        const search = store.adminFilters.search.toLowerCase();
        tickets = tickets.filter(t =>
            t.description?.toLowerCase().includes(search) ||
            t.caller?.name?.toLowerCase().includes(search) ||
            t.type?.name?.toLowerCase().includes(search)
        );
    }

    return tickets;
};

store.ownTicket = (ticketId) => {
    if (store.loading.action) return Promise.reject('Action in progress');

    store.loading.action = true;
    store.errors.action = null;

    return axios.post(`${store.base_url}/api/tickets/${ticketId}/own`)
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
            store.errors.action = error.response?.data?.message || 'Failed to assign ticket.';
            throw error;
        });
};

store.resolveTicket = (ticketId) => {
    if (store.loading.action) return Promise.reject('Action in progress');

    store.loading.action = true;
    store.errors.action = null;

    return axios.post(`${store.base_url}/api/tickets/${ticketId}/resolve`)
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
            store.errors.action = error.response?.data?.message || 'Failed to resolve ticket.';
            throw error;
        });
};

store.rejectTicket = (ticketId, reason) => {
    if (store.loading.action) return Promise.reject('Action in progress');

    store.loading.action = true;
    store.errors.action = null;

    return axios.post(`${store.base_url}/api/tickets/${ticketId}/reject`, { reason })
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
            store.errors.action = error.response?.data?.message || 'Failed to reject ticket.';
            throw error;
        });
};

store.reopenTicket = (ticketId) => {
    if (store.loading.action) return Promise.reject('Action in progress');

    store.loading.action = true;
    store.errors.action = null;

    return axios.post(`${store.base_url}/api/tickets/${ticketId}/reopen`)
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
            store.errors.action = error.response?.data?.message || 'Failed to reopen ticket.';
            throw error;
        });
};

store.updateTicketInLists = (ticket) => {
    const userIndex = store.tickets.findIndex(t => t.id === ticket.id);
    if (userIndex !== -1) {
        store.tickets[userIndex] = ticket;
    }

    const adminIndex = store.adminTickets.findIndex(t => t.id === ticket.id);
    if (adminIndex !== -1) {
        store.adminTickets[adminIndex] = ticket;
    }
};

store.getCurrentUserId = () => {
    return window.Laravel?.userId || null;
};

export default store;
