import bootstrap from "./bootstrap";
import RecruitNewMember from "./components/recruit/RecruitNewMember.vue";

Vue.component("recruiting-process", RecruitNewMember);

import VeeValidate from "vee-validate";

Vue.use(VeeValidate);

const app = new Vue({
    el: "#recruiting-container"
});