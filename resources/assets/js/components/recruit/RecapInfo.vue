<template>
    <div class="panel panel-filled">
        <div class="panel-heading">
            <i class="fa fa-exclamation-circle"></i>
            Member Information
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-xs-4">Member Name</div>
                <div class="col-xs-8 text-right">
                    <code>AOD_Rct_{{ store.forum_name }}</code>
                    <button class="btn btn-xs btn-default copy-to-clipboard" type="button"
                            :data-clipboard-text="'AOD_Rct_' + store.forum_name">
                        <i class="fa fa-clone"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import store from './store';

    export default {
        data: function () {
            return {
                store
            }
        },
    }
</script>