<template>
    <div class="panel panel-c-info panel-filled" v-if="store.inDemoMode">
        <div class="panel-body text-uppercase">
            <i class="fa fa-wrench text-info"></i> Training Mode
            <span class="text-muted pull-right">Data will not be saved</span>
        </div>
    </div>
</template>

<script>
    import store from '../store';

    export default {
        data: () => ({
            store
        })
    };
</script>