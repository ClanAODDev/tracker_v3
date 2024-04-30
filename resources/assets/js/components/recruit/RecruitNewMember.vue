<template>
  <div>
    <progress-bar></progress-bar>
    <step-one v-show="store.currentStep === 'step-one'" :ranks="this.ranks"></step-one>
    <step-two v-show="store.currentStep === 'step-two'"></step-two>
    <step-three v-show="store.currentStep === 'step-three'"></step-three>
    <step-four v-show="store.currentStep === 'step-four'"></step-four>
  </div>
</template>

<script>
import store from './store.js';

import ProgressBar from './ProgressBar.vue';
import StepOne from './StepOne.vue';
import StepTwo from './StepTwo.vue';
import StepThree from './StepThree.vue';
import StepFour from './StepFour.vue';

export default {

  components: {
    StepOne, StepTwo, StepThree, StepFour, ProgressBar
  },

  data() {
    return {
      store
    };
  },

  props: ['division', 'handleName', 'ranks', 'recruiter_id'],

  mounted() {
    this.prepareRecruitingProcess();
  },

  methods: {
    prepareRecruitingProcess: function () {
      store.handleName = this.handleName;
      store.division.slug = this.division;
      store.recruiter_id = this.recruiter_id;
      store.getPlatoons(this.division);
      store.getTasks(this.division);
    }
  },
};
</script>