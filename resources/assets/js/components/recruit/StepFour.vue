<template>
    <div>
        <request-member-status></request-member-status>
        <create-welcome-post v-if="store.division.settings.welcome_area !== undefined"></create-welcome-post>

        <hr />
        <a :href="base_path + '/v3tracker/'" class="btn btn-success pull-right">Finish</a>
        <button class="pull-left btn btn-default" type="button" @click="store.currentStep = 'step-three'">Back</button>
    </div>
</template>
<script>
    import store from '../store.js';
    import RequestMemberStatus from './RequestMemberStatus.vue';
    import CreateWelcomePost from './CreateWelcomePost.vue';

    export default {

        components: {
            'request-member-status': RequestMemberStatus,
            'create-welcome-post': CreateWelcomePost
        },

        data: function () {
            return {
                store
            }
        },

        methods: {
            validateStep: function () {

            }
        }
    }
</script>