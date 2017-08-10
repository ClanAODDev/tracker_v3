<template>
    <form @submit.prevent="validateStep">

        <demo-mode-notice></demo-mode-notice>

        <div class="panel">
            <h3><i class="fa fa-check-circle-o text-accent"></i> Step 3: In-processing</h3>
            <p>You are almost finished with your recruit. Below are tasks required by your division in order to in-process your new member.</p>
        </div>

        <div class="row">
            <div class="col-sm-6">

                <table class="table table-hover table-striped">

                    <tbody>
                    <tr v-for="(task, index) in store.division.tasks" @click="task.complete = !task.complete">
                        <td>
                            <input type="checkbox" :checked="task.complete">
                        </td>
                        <td>
                            <div v-html="task.description"></div>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </div>
            <div class="col-sm-6">
                <recap-info></recap-info>
            </div>
        </div>

        <ts-info></ts-info>

        <hr />

        <p class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i>
            <strong>Heads up!</strong> Continuing past this step will add your recruit to the Tracker. Please ensure they understand and agree to all of AOD's requirements before continuing further in the process.
        </p>
        <hr />

        <button type="submit" class="btn btn-success pull-right">Continue</button>
        <button class="pull-left btn btn-default" type="button" disabled>Back</button>
    </form>
</template>
<script>
  import store from './store.js';

  import TSInfo from './TsInfo.vue';
  import RecapInfo from './RecapInfo.vue';
  import DemoModeNotice from './DemoModeNotice.vue';
  import ProgressBar from './ProgressBar.vue';

  export default {
    data: function () {
      return {
        store
      };
    },

    components: {
      'demo-mode-notice': DemoModeNotice,
      'ts-info': TSInfo,
      'recap-info': RecapInfo,
      'progress-bar': ProgressBar
    },

    methods: {
      validateStep: function () {
        let complete = true;
        store.division.tasks.forEach(function (task) {
          if (!task.complete) {
            complete = false;
          }
        });

        if (store.inDemoMode) {
          store.currentStep = 'step-four';
          store.progress = 100;
          return;
        }

        if (!complete) {
          toastr.error('Mark all tasks complete as you do them');
          return;
        }

        store.currentStep = 'step-four';
        store.createMember();
        store.progress = 100;
      },
    }
  };
</script>