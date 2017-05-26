<template>
    <div>
        <h3 class="m-t-xl"><i class="fa fa-mail-reply-all text-accent"></i> Step 5: Create Welcome Post</h3>

        <div v-if="store.division.settings.use_welcome_thread">
            <p>This division uses a welcome thread for all new recruit introductions. Click the button below to create a post and introduce your new recruit to the other members of the division.</p>

            <div class="text-center p-lg bs-example">
                <a :href="'https://www.clanaod.net/forums/showthread.php?goto=newpost&t=' + store.division.settings.welcome_area
                   target="_blank" class="btn btn-accent">
                    <i class="fa fa-external-link text-accent" aria-hidden="true"></i> Create Post
                </a>
            </div>

        </div>

        <div v-else>
            <p>This division uses a welcome forum for all new recruit introductions. Click the button below to create a thread and introduce your new recruit to the other members of the division.</p>

            <div class="text-center p-lg bs-example">
                <a :href="'https://www.clanaod.net/forums/newthread.php?do=newthread&f=' + store.division.settings.welcome_area"
                   target="_blank" class="btn btn-accent">
                    <i class="fa fa-external-link text-accent" aria-hidden="true"></i> Create Thread
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import store from '../store.js';

    export default {
        data: function () {
            return {
                store
            }
        },
    }
</script>