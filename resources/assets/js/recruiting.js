require('./bootstrap');

Vue.component(
    'recruiting-process',
    require('./components/recruit/RecruitNewMember.vue')
);

import VeeValidate from 'vee-validate';

Vue.use(VeeValidate);

const app = new Vue({
    el: '#recruiting-container'
});

Vue.config.devtools = false;
Vue.config.debug = false;
Vue.config.silent = true;