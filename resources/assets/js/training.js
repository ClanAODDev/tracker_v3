require('./bootstrap');

Vue.component(
  'training-module',
  require('./components/training/Train.vue')
);

import VeeValidate from 'vee-validate';
Vue.use(VeeValidate);

const app = new Vue({
  el: '#training-container'
});