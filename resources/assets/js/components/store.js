let store = {};

export default store

// are we in test mode?
store.inTestMode = false;

// member data
store.member_id = '';
store.ingame_name = '';
store.forum_name = '';
store.platoon = '';
store.squad = '';

store.division = {
    platoons: [],
    squads: []
};

// locality data
store.locality = {
    platoon: 'Platoon',
    platoons: 'Platoons',
    squad: 'Squad',
    squads: 'Squads',
};

/**
 * SomeMethod
 */
store.getDivisionData = (division) => {

    axios.get('/division-platoons-squads/' + division)
        .then(function (response) {
            store.division.platoons = response.data.data.platoons;
        })
        .catch(function (error) {
            console.log(error);
        });
};

store.getPlatoonSquads = (platoon) => {
    axios.post('/search-platoon/', {
        platoon: platoon
    }).then(function (response) {
        store.division.squads = response.data;
    }).catch(function (error) {
        //
    });
};
