<template>
    <form @submit.prevent="validateStep">

        <demo-mode-notice></demo-mode-notice>

        <h3><i class="fa fa-pencil-square-o text-accent"></i> Step 2: Member Agreement</h3>

        <p>AOD members are required to read and reply to a handful of threads posts in the AOD community forums. Your division may have additional threads that you require new members to reply to.</p>

        <button class="btn btn-default refresh-button m-t-lg"
                :disabled="store.loadingThreads"
                @click="store.getDivisionThreads(store.division.abbreviation)">
            <i class="fa fa-refresh text-info" :class="(store.loadingThreads) ? 'fa-spin' : null"></i> <span
                class="status">Run Thread Check...</span>
        </button>

        <hr />
        <division-threads v-if=" ! store.loadingThreads"></division-threads>
        <div v-else><p>Searching threads for posts by member <code>#{{ store.member_id }}</code></p></div>
        <div class="text-muted" v-if="! store.division.threads.length && ! store.loadingThreads">
            <p>Run the thread check to poll the AOD forums.</p>
        </div>
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
            }
        },

        methods: {
            validateStep: function () {
                if (store.threadsIncomplete) {
                    toastr.error('Recruit has not completed all threads!', 'Uh oh...');
                    return;
                }

                store.currentStep = 'step-three';
                store.progress = 75;
            },

            backToStepOne: () => {
                store.progress = 25;
                store.currentStep = 'step-one';
            }
        },
    }
</script>