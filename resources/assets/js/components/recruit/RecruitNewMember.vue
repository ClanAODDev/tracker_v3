<template>
    <step-one></step-one>
</template>

<style>

</style>

<script>
    import store from '../store.js';

    import StepOne from './StepOne.vue';

    export default {

        components: {
            StepOne
        },

        data() {
            return {
                store
            }

        },

        props: ['division'],

        mounted () {
            this.prepareRecruitingProcess()
        },

        computed: {

        },

        methods: {
            prepareRecruitingProcess: function () {
                store.getDivisionData(this.division);
            }
        },
    }
</script>