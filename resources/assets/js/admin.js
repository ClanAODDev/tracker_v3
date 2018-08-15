require('./bootstrap');

Vue.component(
  'member-requests',
  require('./components/admin/ApproveRequests.vue')
);

const app = new Vue({
  el: '#admin-container'
});