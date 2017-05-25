let store = {};
let base_url = window.Laravel.appPath;

export default store

// are we in test mode?
store.inDemoMode = false;
store.loadingThreads = false;

// step
store.currentStep = 'step-one';

// member data
store.member_id = '';
store.ingame_name = '';
store.forum_name = '';
store.platoon = '';
store.squad = '';

// division data
store.division = {
    abbreviation: '',
    platoons: [],
    squads: [],
    threads: [],
    tasks: [],
};

// locality data
store.locality = {
    platoon: 'Platoon',
    platoons: 'Platoons',
    squad: 'Squad',
    squads: 'Squads',
};


/**
 * fetches basic division data for recruiting
 *
 * @param division
 */
store.getDivisionData = (division) => {
    axios.get(base_url + '/division-platoons/' + division)
        .then(function (response) {
            store.division.platoons = response.data.data.platoons;
        })
        .catch(function (error) {
            toastr.error(error, 'Something went wrong!')
        });
};

store.getDivisionThreads = (division) => {
    store.loadingThreads = true;
    axios.post(base_url + '/search-division-threads', {
        division: division,
        isTesting: store.inDemoMode,
    }).then(function (response) {
        store.loadingThreads = false;
        store.division.threads = response.data;
    }).catch(function (error) {
        toastr.error(error, 'Something went wrong!')
    });
};

store.getPlatoonSquads = (platoon) => {
    axios.post(base_url + '/platoon-squads/', {
        platoon: platoon
    }).then(function (response) {
        store.division.squads = response.data;
    }).catch(function (error) {
        toastr.error(error, 'Something went wrong!')
    });
};
