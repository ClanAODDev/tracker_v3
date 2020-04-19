<template>
    <div class="thread-list">

        <div class="panel panel-filled panel-c-info thread" style="cursor: pointer;"
             v-for="thread in store.division.threads">
            <div class="panel-heading text-uppercase" @click="toggleItem(thread.thread_id)">
                {{ thread.thread_name }}

                <button class="btn btn-xs btn-default copy-to-clipboard" type="button"
                        :data-clipboard-text="thread.url">
                    <i class="fa fa-clone"></i>
                </button>

                <span class="pull-right">
                       <i :class="toggle.indexOf(thread.thread_id) >= 0
                           ? 'fa text-success fa-2x fa-check-circle'
                           : 'fa text-danger fa-2x fa-times-circle'"
                       />
                </span>
            </div>

            <div class="panel-body m-t-n" v-if="thread.comments">
                <span v-html="thread.comments"></span>
            </div>
        </div>
    </div>
</template>

<script>
    import store from './store';

    export default {
        data: () => ({
            toggle: [],
            store
        }),
        methods: {
            toggleItem: function (key) {
                var i = this.toggle.indexOf(key)
                if (i < 0) {
                    this.toggle.push(key)
                } else {
                    this.toggle.splice(i, 1)
                }
            }
        }
    };
</script>