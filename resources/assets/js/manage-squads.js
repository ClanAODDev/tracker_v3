require('./bootstrap');
var Squad = require('./components/manage/Squad.vue');

const app = new Vue({
  el: '#manage-platoon',
  components: {
    squad: Squad
  }
});

Vue.config.devtools = false;
Vue.config.debug = false;
Vue.config.silent = true;