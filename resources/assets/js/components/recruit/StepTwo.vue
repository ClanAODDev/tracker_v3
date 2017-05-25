<template>
    <div class="step-container step-two">

        <demo-mode-notice></demo-mode-notice>

        <h3><i class="fa fa-pencil-square-o text-accent"></i> Step 2: Member Agreement</h3>

        <p>AOD members are required to read and reply to a handful of threads posts in the AOD community forums. Your division may have additional threads that you require new members to reply to.</p>

        <button class="btn btn-default refresh-button m-t-lg"
                @click="store.getDivisionThreads(store.division.abbreviation)">
            <i class="fa fa-refresh text-info" :class="(store.loadingThreads) ? 'fa-spin' : null"></i> <span
                class="status">Run Thread Check...</span>
        </button>

        <div class="thread-results"></div>
        <hr />

        <division-threads v-if=" ! store.loadingThreads"></division-threads>
        <div v-else><p>Searching threads for posts by member <code>#{{ store.member_id }}</code></p></div>
        <div v-if="! store.division.threads.length && ! store.loadingThreads">Run the thread check to poll the AOD forums.</div>
        <hr />

        <button class="pull-right step-two-submit btn btn-success" type="button">Continue</button>
        <button class="pull-left btn btn-default" type="button" @click="backToStepOne">Back</button>
    </div>
</template>

<script>
    import store from '../store';
    import DemoModeNotice from './DemoModeNotice.vue';
    import DivisionThreads from './DivisionThreads.vue';

    export default {
        components: {
            'demo-mode-notice': DemoModeNotice,
            'division-threads': DivisionThreads,
        },

        data() {
            return {
                store
            }
        },

        methods: {
            backToStepOne: () => {
                store.currentStep = 'step-one';
            }
        },

        mounted() {

        }
    }
</script>