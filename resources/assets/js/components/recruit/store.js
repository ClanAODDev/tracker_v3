import { reactive } from 'vue';

const store = reactive({
    base_url: window.Laravel.appPath,
    currentStep: 'form',
    inDemoMode: false,
    submitting: false,
    submitted: false,
    recruiter_id: null,
    rankLabels: {},

    member: {
        id: '',
        forum_name: '',
        ingame_name: '',
        rank: '',
        platoon: '',
        squad: '',
    },

    validation: {
        loading: false,
        memberId: {
            valid: false,
            verifiedEmail: false,
            groupId: null,
            currentUsername: '',
            existsInTracker: false,
            tags: [],
            division: null,
        },
        forumName: {
            valid: false,
            available: false,
        },
    },

    division: {
        slug: '',
        name: '',
        platoons: [],
        threads: [],
        tasks: [],
        welcome_area: '',
        welcome_pm: '',
        use_welcome_thread: false,
        locality: {
            platoon: 'Platoon',
            squad: 'Squad',
        },
        pending_discord: [],
    },

    selectedPendingUser: null,

    loading: {
        divisionData: false,
    },

    errors: {
        divisionData: null,
        submission: null,
    },
});

let debounceTimers = {};

function debounce(key, fn, delay = 300) {
    if (debounceTimers[key]) {
        clearTimeout(debounceTimers[key]);
    }
    debounceTimers[key] = setTimeout(fn, delay);
}

store.loadDivisionData = (divisionSlug) => {
    store.loading.divisionData = true;
    store.errors.divisionData = null;
    store.division.slug = divisionSlug;

    return axios.get(`${store.base_url}/divisions/${divisionSlug}/recruit/data`)
        .then((response) => {
            const data = response.data;
            store.division.name = data.name || '';
            store.division.platoons = data.platoons || [];
            store.division.threads = data.threads || [];
            store.division.tasks = data.tasks || [];
            store.division.welcome_area = data.welcome_area || '';
            store.division.welcome_pm = data.welcome_pm || '';
            store.division.use_welcome_thread = data.use_welcome_thread || false;
            store.division.locality = data.locality || { platoon: 'Platoon', squad: 'Squad' };
            store.division.pending_discord = data.pending_discord || [];
            store.loading.divisionData = false;
        })
        .catch((error) => {
            store.loading.divisionData = false;
            store.errors.divisionData = 'Failed to load division data. Please refresh and try again.';
            console.error('Division data load error:', error);
        });
};

store.selectPendingUser = (userId) => {
    if (!userId) {
        store.selectedPendingUser = null;
        return;
    }

    const user = store.division.pending_discord.find(u => u.id === parseInt(userId));
    if (user) {
        store.selectedPendingUser = user;
        store.member.forum_name = user.discord_username;
        store.member.rank = '1';
        store.validation.forumName = { valid: false, available: false };
        store.validateForumName(user.discord_username, store.member.id);
    }
};

store.clearPendingUser = () => {
    store.selectedPendingUser = null;
};

store.getSquadsForPlatoon = (platoonId) => {
    const platoon = store.division.platoons.find(p => p.id === parseInt(platoonId));
    return platoon ? platoon.squads : [];
};

store.validateMemberId = (memberId) => {
    if (!memberId || memberId.length < 1) {
        store.validation.memberId = { valid: false, verifiedEmail: false, groupId: null, currentUsername: '', existsInTracker: false, tags: [], division: null };
        return;
    }

    store.validation.loading = true;

    debounce('memberId', () => {
        if (store.inDemoMode) {
            store.validation.memberId = { valid: true, verifiedEmail: true, groupId: null, currentUsername: 'DemoUser', existsInTracker: false, tags: [], division: null };
            store.member.forum_name = 'DemoUser';
            store.validation.forumName = { valid: true, available: true };
            store.validation.loading = false;
            return;
        }

        axios.post(`${store.base_url}/validate-id/${memberId}`)
            .then((response) => {
                const data = response.data;
                store.validation.memberId = {
                    valid: data.is_member || false,
                    verifiedEmail: data.valid_group || false,
                    groupId: data.group_id || null,
                    currentUsername: data.username || '',
                    existsInTracker: data.exists_in_tracker || false,
                    tags: data.tags || [],
                    division: data.division || null,
                };

                if (data.is_member && data.valid_group && data.username) {
                    const forumName = data.username.replace(/^AOD_/i, '');
                    store.member.forum_name = forumName;
                    store.validateForumName(forumName, memberId);
                }

                store.validation.loading = false;
            })
            .catch(() => {
                store.validation.memberId = { valid: false, verifiedEmail: false, groupId: null, currentUsername: '', existsInTracker: false, tags: [], division: null };
                store.validation.loading = false;
            });
    }, 300);
};

store.validateForumName = (name, memberId) => {
    if (!name || name.length < 1) {
        store.validation.forumName = { valid: false, available: false };
        return;
    }

    const hasAodPrefix = name.toLowerCase().startsWith('aod_');
    if (hasAodPrefix) {
        store.validation.forumName = { valid: false, available: false };
        return;
    }

    store.validation.loading = true;

    debounce('forumName', () => {
        if (store.inDemoMode) {
            store.validation.forumName = { valid: true, available: true };
            store.validation.loading = false;
            return;
        }

        const params = { name, member_id: memberId };
        if (store.selectedPendingUser?.email) {
            params.email = store.selectedPendingUser.email;
        }

        axios.post(`${store.base_url}/validate-name`, params)
            .then((response) => {
                const available = !response.data.memberExists;
                const existingAccount = response.data.existingAccount || false;
                store.validation.forumName = { valid: available, available, existingAccount };

                if (existingAccount && response.data.existingUserId) {
                    store.member.id = String(response.data.existingUserId);
                    store.selectedPendingUser = null;
                    store.validation.memberId = {
                        valid: true,
                        verifiedEmail: true,
                        groupId: null,
                        currentUsername: name,
                        existsInTracker: false,
                        tags: [],
                        division: null,
                    };
                }

                store.validation.loading = false;
            })
            .catch(() => {
                store.validation.forumName = { valid: false, available: false };
                store.validation.loading = false;
            });
    }, 300);
};

store.isFormValid = () => {
    const platoon = store.division.platoons.find(p => p.id === parseInt(store.member.platoon));
    const hasSquads = platoon ? platoon.squads.length > 0 : false;
    const squadValid = !hasSquads || store.member.squad;

    const hasPendingUser = store.selectedPendingUser !== null;
    const memberIdValid = hasPendingUser || (store.member.id && store.validation.memberId.valid && store.validation.memberId.verifiedEmail);

    return memberIdValid &&
           store.member.forum_name &&
           store.member.ingame_name &&
           store.member.rank &&
           store.member.platoon &&
           squadValid &&
           store.validation.forumName.valid;
};

store.submitRecruitment = () => {
    if (!store.isFormValid() || store.submitting) {
        return Promise.reject('Form is not valid');
    }

    store.submitting = true;
    store.errors.submission = null;

    if (store.inDemoMode) {
        return new Promise((resolve) => {
            setTimeout(() => {
                store.submitting = false;
                store.submitted = true;
                store.currentStep = 'confirmation';
                resolve();
            }, 500);
        });
    }

    const payload = {
        division: store.division.slug,
        member_id: store.member.id,
        forum_name: store.member.forum_name,
        ingame_name: store.member.ingame_name,
        platoon: store.member.platoon,
        rank: store.member.rank,
        squad: store.member.squad,
        pending_user_id: store.selectedPendingUser?.id || null,
    };

    return axios.post(`${store.base_url}/add-member/`, payload)
    .then(() => {
        store.submitting = false;
        store.submitted = true;
        store.currentStep = 'confirmation';
    })
    .catch((error) => {
        store.submitting = false;
        store.errors.submission = error.response?.data?.message || 'Failed to add recruit. Please try again.';
        throw error;
    });
};

store.resetForNewRecruit = () => {
    store.currentStep = 'form';
    store.submitting = false;
    store.submitted = false;
    store.errors.submission = null;
    store.selectedPendingUser = null;

    store.member.id = '';
    store.member.forum_name = '';
    store.member.ingame_name = '';
    store.member.rank = '';
    store.member.platoon = '';
    store.member.squad = '';

    store.validation.loading = false;
    store.validation.memberId = { valid: false, verifiedEmail: false, groupId: null, currentUsername: '', existsInTracker: false, tags: [], division: null };
    store.validation.forumName = { valid: false, available: false };
};

store.toggleDemoMode = () => {
    store.inDemoMode = !store.inDemoMode;
    if (store.inDemoMode) {
        store.member.id = '99999';
        store.member.forum_name = 'TestRecruit';
        store.member.ingame_name = 'TestHandle';
        store.member.rank = '1';
        store.validation.memberId = { valid: true, verifiedEmail: true, groupId: null, currentUsername: 'TestUser', existsInTracker: false, tags: [], division: null };
        store.validation.forumName = { valid: true, available: true };
    } else {
        store.resetForNewRecruit();
    }
};

store.getFormattedName = () => {
    if (!store.member.forum_name) return '';
    return `AOD_${store.member.forum_name}`;
};

window.onbeforeunload = (e) => {
    if (store.currentStep === 'form' && (store.member.id || store.member.forum_name)) {
        const dialogText = 'You are about to leave the recruiting process. Are you sure?';
        e.returnValue = dialogText;
        return dialogText;
    }
};

export default store;
