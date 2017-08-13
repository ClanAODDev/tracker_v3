<template>
    <div>
        <demo-mode-notice></demo-mode-notice>
        <request-member-status></request-member-status>
        <create-welcome-post v-if="store.division.settings.welcome_area != ''"></create-welcome-post>
        <send-welcome-pm v-if="store.division.settings.welcome_pm != ''"></send-welcome-pm>

        <hr />
        <button class="pull-right btn btn-success" type="button" @click="validateStep">Finish</button>
        <button class="pull-left btn btn-default" type="button" disabled>Back</button>
    </div>
</template>

<script>
  import store from './store.js';
  import RequestMemberStatus from './RequestMemberStatus.vue';
  import CreateWelcomePost from './CreateWelcomePost.vue';
  import SendWelcomePM from './SendWelcomePM.vue';
  import DemoModeNotice from './DemoModeNotice.vue';

  export default {

    components: {
      'request-member-status': RequestMemberStatus,
      'create-welcome-post': CreateWelcomePost,
      'demo-mode-notice': DemoModeNotice,
      'send-welcome-pm': SendWelcomePM,
    },

    methods: {
      validateStep: function () {
        if (!store.didUserOpenRequest) {
          toastr.error('You must request member status to complete this process', 'Heads up!');
          return;
        }

        store.progress = 100;
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