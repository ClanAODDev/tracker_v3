<template>
    <div>
        <demo-mode-notice></demo-mode-notice>
        <request-member-status></request-member-status>
        <create-welcome-post v-if="store.division.settings.welcome_area !== undefined"></create-welcome-post>

        <hr />
        <button class="pull-right btn btn-success" type="button" @click="validateStep">Finish</button>
        <button class="pull-left btn btn-default" type="button" @click="store.currentStep = 'step-three'">Back</button>
    </div>
</template>

<script>
  import store from './store.js';
  import RequestMemberStatus from './RequestMemberStatus.vue';
  import CreateWelcomePost from './CreateWelcomePost.vue';
  import DemoModeNotice from './DemoModeNotice.vue';

  export default {

    components: {
      'request-member-status': RequestMemberStatus,
      'create-welcome-post': CreateWelcomePost,
      'demo-mode-notice': DemoModeNotice
    },

    methods: {
      validateStep: function () {
        if (!store.didUserOpenRequest) {
          toastr.error('You must request member status to complete this process', 'Heads up!');
          return;
        }

        store.progress = 100;
        store.createMember();
        window.location.href = store.base_url;
      }
    },

    data: function () {
      return {
        store
      };
    },
  };
</script>