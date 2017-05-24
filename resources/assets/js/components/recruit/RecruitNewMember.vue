<template>
    <div>
        <step-one></step-one>
        <!--<step-two></step-two>-->
        <!--<step-three></step-three>-->
        <!--<step-four></step-four>-->
        <!--<step-five></step-five>-->
    </div>
</template>

<script>
    import store from '../store.js';

    import StepOne from './StepOne.vue';
    //    import StepTwo from './StepOne.vue';
    //    import StepOne from './StepOne.vue';
    //    import StepOne from './StepOne.vue';

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

        methods: {
            prepareRecruitingProcess: function () {
                store.getDivisionData(this.division);
            }
        },
    }
</script>