require('./bootstrap');

Vue.component(
  'ManageMember',
    () => import('./components/member-edit/ManageMember.vue')
);

Vue.component(
  'ManageHandles',
    () => import('./components/member-edit/ManageHandles.vue')
);

const app = new Vue({
  el: '#profile-container'
});
