<template>
    <div>
        <h3><i class="fa fa-plus-square text-accent"></i> Step 4: Member Status Request</h3>

        <p>Your new recruit has been added to the Tracker, but you still need to submit a member status request to have their AOD permissions approved. To do that, use the button below to open the status request form.</p>

        <p>Note: if you need to change the member's forum name, include it as the "name" in your request. Otherwise,
            <span class="text-accent">leave the name field blank</span>.</p>

        <div class="row p-lg bs-example">
            <div class="col-sm-6 text-center">
                <label>Forum User ID</label>
                <code>{{ store.member_id}}</code>
                <button class="btn btn-xs btn-default copy-to-clipboard" type="button"
                        :data-clipboard-text="store.member_id">
                    <i class="fa fa-clone"></i>
                </button>
            </div>

            <div class="col-sm-6 text-center">
                <div class="m-t-md visible-xs-block"></div>
                <a href="http://www.clanaod.net/forums/misc.php?do=form&fid=39" target="_blank"
                   :class="store.didUserOpenRequest ? 'btn btn-success' : 'btn btn-danger'"
                   @click="userOpenedRequest">
                    <i :class="store.didUserOpenRequest ? 'fa fa-check text-success' : 'fa fa-times text-danger'"
                       aria-hidden="true"></i> Open Member Status Form
                </a>
            </div>
        </div>
    </div>

</template>

<script>
    import store from './store.js';
    import ProgressBar from './ProgressBar.vue';

    export default {
        components: {
            'progress-bar': ProgressBar
        },
        methods: {
            userOpenedRequest: function () {
                store.didUserOpenRequest = true;
            }
        },
        data: function () {
            return {
                didUserOpenRequest: false,
                store
            }
        },
    }
</script>