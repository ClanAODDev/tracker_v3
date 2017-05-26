<template>
    <form @submit.prevent="validateStep">

        <div class="panel">
            <h3><i class="fa fa-check-circle-o text-accent"></i> Step 3: In-processing</h3>
            <progress-bar progress="60"></progress-bar>
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
                            <label :for="'task' + index">{{ task.description }}</label>
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

        <button type="submit" class="btn btn-success pull-right">Continue</button>
        <button class="pull-left btn btn-default" type="button" disabled>Back</button>
    </form>
</template>
<script>
    import store from '../store.js';

    import TSInfo from './TsInfo.vue';
    import RecapInfo from './RecapInfo.vue';
    import DemoModeNotice from './DemoModeNotice.vue';
    import ProgressBar from './ProgressBar.vue';

    export default {
        data: function () {
            return {
                store
            }
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

                if (!complete && !store.inDemoMode) {
                    toastr.error('Mark all tasks complete as you do them');
                    return;
                }

                store.currentStep = 'step-four';
            },
        }
    }
</script>