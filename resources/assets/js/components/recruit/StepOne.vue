<template>
    <div class="step-container step-one">

        <div class="alert alert-warning" style="cursor: pointer"
             onclick="window.Recruiting.scrollTo('.has-error:first-of-type')">
            There was a problem with your recruitment. Please review the issues marked in red.
            <i class="fa fa-arrow-circle-right"></i>
        </div>

        <h3>Getting Started</h3>

        <p>This is an introductory content paragraph for divisions to provide specific instructions regarding a recruitment process. This is a good place to talk about division-specific policies that may make or break interest, ie., division in-game requirements, must join platoon, must wear tags, etc.</p>
        <p>Additionally, recruiters should mention the clan-wide membership requirements:</p>
        <ul class="c-white">
            <li>Maintain minimum forum activity. Inactivity can result in removal from AOD</li>
            <li>Engage on TeamSpeak when playing a game AOD supports</li>
            <li>Strive to be a contributing member of your division</li>
            <li>Always be respectful of other clan members and leadership</li>
        </ul>

        <h3 class="m-t-xl"><i class="fa fa-address-card text-accent" aria-hidden="true"></i> Step 1: Member Data</h3>
        <hr />

        <div class="alert text-center">
            <i class="fa fa-question-circle text-accent"></i>
            Training, or just want to look around?
            <button type="button" name="trngMode" v-on:click="setDummyData"
                    class="btn btn-rounded btn-accent btn-sm">Use Dummy Data
            </button>
        </div>

        <member-information></member-information>
        <member-assignment></member-assignment>

        <button type="submit" class="btn btn-success pull-right">Continue <i class="fa fa-arrow-right"></i></button>
    </div>
</template>

<script>
    import Store from '../store.js';

    import MemberInformation from './MemberInformationForm.vue';
    import MemberAssignment from './MemberAssignmentForm.vue';

    export default {

        components: {
            'member-information': MemberInformation,
            'member-assignment': MemberAssignment,
        },

        methods: {
            /**
             * Provide dummy data for training
             */
            setDummyData: function () {
                Store.member_id = 99999;
                Store.forum_name = "test-user";
                Store.ingame_name = "test-user";
            },
        },

        mounted() {
        },

        data() {
            return {
                Store
            }
        }
    }

</script>