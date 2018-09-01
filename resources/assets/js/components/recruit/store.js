/**
 * JS Store
 * Using a single source of truth to act as a DTO between
 * Vue components. This allows us to simplify the process
 * of maintaining application state
 */

let store = {};

export default store;

store.base_url = window.Laravel.appPath;

/**
 * =====================
 * Recruiting handlers
 * =====================
 */

// are we in test mode?
store.inDemoMode = false;
store.loadingThreads = false;
store.threadsIncomplete = true;

// application states
store.currentStep = 'step-one';
store.progress = 25;

// member data
store.member_id = '';
store.ingame_name = '';
store.rank = '';
store.forum_name = '';
store.platoon = '';
store.squad = '';
store.validMemberId = false;

// division data
store.handleName = '';
store.division = {
  abbreviation: '',
  settings: [],
  platoons: [],
  squads: [],
  threads: [],
  tasks: []
};

// locality data
store.locality = {
  platoon: 'Platoon',
  platoons: 'Platoons',
  squad: 'Squad',
  squads: 'Squads',
};

/**
 * fetches platoons for recruiting
 *
 * @param division
 */
store.getPlatoons = (division) => {
  axios.get(store.base_url + '/division-platoons/' + division)
    .then(function (response) {
      store.division.platoons = response.data.data.platoons;
      store.division.settings = response.data.data.settings;
    })
    .catch(function (error) {
      toastr.error(error, 'Something went wrong while fetching division platoons');
    });
};

/**
 * has the recruit responded to all threads?
 *
 * @param threads
 */
store.checkIfIncomplete = function (threads) {
  threads.forEach(function (thread) {
    if (!thread.status) {
      store.threadsIncomplete = true;
      return;
    }
    store.threadsIncomplete = false;
  });
};

/**
 * fetches a division's required agreement threads
 *
 * @param division
 */
store.getDivisionThreads = (division) => {
  store.loadingThreads = true;
  axios.post(store.base_url + '/search-division-threads', {
    division: division,
    string: store.member_id,
    isTesting: store.inDemoMode,
  }).then(function (response) {
    store.loadingThreads = false;
    store.division.threads = response.data;
    store.checkIfIncomplete(store.division.threads);
  }).catch(function (error) {
    toastr.error(error, 'Something went wrong while fetching division threads');
  });
};

/**
 * fetches a platoon's squads
 *
 * @param platoon
 */
store.getPlatoonSquads = (platoon) => {
  if (store.platoon == '') {
    return;
  }

  axios.post(store.base_url + '/platoon-squads/', {
    platoon: platoon
  }).then(function (response) {
    store.division.squads = response.data;
  }).catch(function (error) {
    toastr.error(error, 'Something went wrong while fetching platoon squads');
  });
};

/**
 * fetch a division's recruiting tasks
 *
 * @param division
 */
store.getTasks = (division) => {
  axios.post(store.base_url + '/division-tasks/', {
    division: division
  }).then(function (response) {
    store.division.tasks = response.data;
  }).catch(function (error) {
    toastr.error(error, 'Division tasks could not be retrieved');
  });
};

/**
 * pushes a request to create a new member
 */
store.createMember = () => {
  if (!store.inDemoMode) {
    axios.post(store.base_url + '/add-member/', {
      division: store.division.abbreviation,
      member_id: store.member_id,
      forum_name: store.forum_name,
      ingame_name: store.ingame_name,
      platoon: store.platoon,
      rank: store.rank,
      squad: store.squad
    }).then(function (response) {
      toastr.success('Your recruit has been added to the tracker and a member status has been submitted!');
    }).catch(function (error) {
      toastr.error(error, 'The creation process could not be completed...');
    });
  }
};

window.onbeforeunload = function (e) {
  if (store.currentStep !== 'step-four') {
    let dialogText = 'You are about to leave the recruiting process. Are you sure?';
    e.returnValue = dialogText;
    return dialogText;
  }
};

/**
 * =====================
 * End Recruiting
 * =====================
 */
