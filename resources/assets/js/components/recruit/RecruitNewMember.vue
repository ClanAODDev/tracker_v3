<template>
    <div>
        <progress-bar></progress-bar>
        <step-one v-show="store.currentStep === 'step-one'"></step-one>
        <step-two v-show="store.currentStep === 'step-two'"></step-two>
        <step-three v-show="store.currentStep === 'step-three'"></step-three>
        <step-four v-show="store.currentStep === 'step-four'"></step-four>
    </div>
</template>

<script>
  import store from './store.js';
  import toastr from 'toastr';

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

    props: ['division'],

    mounted () {
      this.prepareRecruitingProcess();
    },

    methods: {
      prepareRecruitingProcess: function () {
        store.division.abbreviation = this.division;
        store.getPlatoons(this.division);
        store.getTasks(this.division);
      }
    },
  };
</script>