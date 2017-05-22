require('./bootstrap');

Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue')
);

const app = new Vue({
    el: '#recruiting-process'
});