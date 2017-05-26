<template>
    <div class="thread-list">

        <div class="panel panel-filled thread" :class="(thread.status) ? 'panel-c-success' : 'panel-c-danger'"
             v-for="thread in store.division.threads">
            <div class="panel-heading text-uppercase">
                {{ thread.thread_name }}

                <button class="btn btn-xs btn-default copy-to-clipboard" type="button"
                        :data-clipboard-text="thread.url">
                    <i class="fa fa-clone"></i>
                </button>

                <span class="pull-right">
                        <i class="fa text-success fa-2x fa-check-circle" v-if="thread.status"></i>
                        <i class="fa text-danger fa-2x fa-times-circle" v-else></i>
                </span>
            </div>

            <div class="panel-body m-t-n" v-if="thread.comments">
                <span v-html="thread.comments"></span>
            </div>
        </div>
    </div>
</template>

<script>
    import store from '../store';

    export default {
        data: () => ({
            store
        })
    }
</script>