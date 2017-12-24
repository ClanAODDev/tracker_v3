require('./bootstrap');

let VueFire = require('vuefire');
let firebase = require('firebase');

Vue.use(VueFire);

let config = {
  apiKey: 'AIzaSyAU_JjtzvzpOJ-Xw4dumG7hbraDxWPnO_0',
  authDomain: 'fireteam-management.firebaseapp.com',
  databaseURL: 'https://fireteam-management.firebaseio.com',
  projectId: 'fireteam-management',
  storageBucket: '',
  messagingSenderId: '589909481967'
};

let firebaseApp = firebase.initializeApp(config);
let db = firebaseApp.database();

let fireteamsRef = db.ref('fireteams');

const app = new Vue({
  el: '#container',
  data: {
    player: {
      'bnet': '',
      'light': 0
    },
    newFireteam: {
      'name': '',
      'players_needed': 1,
      'slots_available': 3,
      'type': 'Raid',
      'description': '',
      'owner_light': 0,
      'owner_bnet': '',
      players: []
    }
  },
  firebase: {
    fireteams: fireteamsRef.limitToLast(25)
  },

  methods: {

    addFireteam: function () {
      if (this.newFireteam) {
        fireteamsRef.push({
          'name': this.newFireteam.name,
          'players_needed': this.newFireteam.players_needed,
          'type': this.newFireteam.type,
          'slots_available': this.newFireteam.players_needed,
          'description': this.newFireteam.description,
          'owner_light': this.newFireteam.owner_light,
          'owner_bnet': this.newFireteam.owner_bnet,
          'players': []
        });
        this.newFireteam.name = '';
        this.newFireteam.players_needed = '';
        this.newFireteam.type = '';
        this.newFireteam.slots_available = 3;
        this.newFireteam.description = '';
        this.newFireteam.owner_light = '';
        this.newFireteam.owner_bnet = '';
        this.newFireteam.players = [];
      }
    },
    joinFireteam: function (fireteam, player) {
      if (fireteam.slots_available > 0) {
        fireteamsRef.child(fireteam['.key']).child('players').push(player);
        fireteamsRef.child(fireteam['.key']).child('players').on('value', function (snapshot) {
          fireteamsRef.child(fireteam['.key']).child('slots_available').set(
            fireteam.players_needed - snapshot.numChildren()
          );
        });
      }

      // console.log(fireteamsRef.child(fireteam['.key']).child('slots_available').set);
      // fireteamsRef.child(fireteam['.key']).child('slots_available')
      //   .set(fireteam.players_needed - Object.keys(fireteam.players));
    },
    cancelFireteam: function (fireteam) {
      fireteamsRef.child(fireteam['.key']).remove();
    }
  }
});