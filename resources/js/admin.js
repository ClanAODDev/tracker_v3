require('./bootstrap');

Vue.component(
  'member-requests',
  require('./components/admin/MemberRequests.vue')
);

const app = new Vue({
  el: '#admin-container'
});