require('./bootstrap');

Vue.component(
    'manage-member',
    require('./components/member-edit/ManageMember.vue')
);

Vue.component(
    'manage-user-account',
    require('./components/member-edit/ManageUser.vue')
);

const app = new Vue({
    el: '#profile-container',

    mounted () {
    }
});