<template>
  <div class="recruit-wizard">
    <step-one
      v-if="store.currentStep === 'form'"
      :ranks="ranks"
      :recruiter-id="recruiterId"
      :division-slug="division"
      :cancel-url="cancelUrl"
    ></step-one>

    <step-confirmation
      v-else-if="store.currentStep === 'confirmation'"
      :division-slug="division"
    ></step-confirmation>
  </div>
</template>

<script>
import store from './store.js';
import StepOne from './StepOne.vue';
import StepConfirmation from './StepConfirmation.vue';

export default {
  components: {
    StepOne,
    StepConfirmation,
  },

  props: ['division', 'ranks', 'recruiterId'],

  data() {
    return {
      store,
    };
  },

  computed: {
    cancelUrl() {
      return `${store.base_url}/divisions/${this.division}`;
    },
  },
};
</script>
