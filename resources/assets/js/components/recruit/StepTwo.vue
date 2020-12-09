<template>
    <form @submit.prevent="validateStep">

        <demo-mode-notice></demo-mode-notice>

        <h3><i class="fa fa-pencil-square-o text-accent"></i> Step 2: Member Agreement</h3>

        <p>AOD members are required to read and reply to posts in the AOD community forums. Your division may have additional threads that you require new members to reply to.</p>

        <hr />
        <division-threads v-if=" ! store.loadingThreads"></division-threads>
        <div v-else><p>Searching threads for posts by member <code>#{{ store.member_id }}</code></p></div>

        <hr />

        <button class="btn btn-success pull-right" type="submit">Continue</button>
        <button class="pull-left btn btn-default" type="button" @click="backToStepOne">Back</button>
    </form>
</template>

<script>
  import store from './store';
  import DemoModeNotice from './DemoModeNotice.vue';
  import DivisionThreads from './DivisionThreads.vue';
  import ProgressBar from './ProgressBar.vue';

  export default {
    components: {
      'progress-bar': ProgressBar,
      'demo-mode-notice': DemoModeNotice,
      'division-threads': DivisionThreads,
    },

    computed: {},

    data: function () {
      return {
        store
      };
    },

    methods: {
      validateStep: function () {
        store.currentStep = 'step-three';
        store.progress = 75;
      },

      backToStepOne: () => {
        store.progress = 25;
        store.currentStep = 'step-one';
      }
    },
  };
</script>