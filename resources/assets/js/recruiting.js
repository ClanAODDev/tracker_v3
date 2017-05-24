require('./bootstrap');

Vue.component(
    'recruiting-process',
    require('./components/recruit/RecruitNewMember.vue')
);


const app = new Vue({
    el: '#recruiting-container'
});