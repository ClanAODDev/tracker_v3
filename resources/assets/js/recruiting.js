import './bootstrap';
import RecruitNewMember from './components/recruit/RecruitNewMember.vue';
import VeeValidate from 'vee-validate';

Vue.component('recruiting-process', RecruitNewMember);
Vue.use(VeeValidate);

const app = new Vue({
    el: '#recruiting-container'
});