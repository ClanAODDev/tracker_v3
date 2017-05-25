<template>
    <div>
        <step-one v-show="store.currentStep === 'step-one'"></step-one>
        <step-two v-show="store.currentStep === 'step-two'"></step-two>
        <!--<step-three></step-three>-->
        <!--<step-four></step-four>-->
        <!--<step-five></step-five>-->
    </div>
</template>

<script>
    import store from '../store.js';
    import toastr from 'toastr';

    import StepOne from './StepOne.vue';
    import StepTwo from './StepTwo.vue';
    //    import StepOne from './StepOne.vue';
    //    import StepOne from './StepOne.vue';

    export default {

        components: {
            StepOne, StepTwo
        },

        data() {
            return {
                store
            }
        },

        props: ['division'],

        mounted () {
            this.prepareRecruitingProcess();
        },

        methods: {
            prepareRecruitingProcess: function () {
                store.division.abbreviation = this.division;
                store.getDivisionData(this.division);
            }
        },
    }
</script>