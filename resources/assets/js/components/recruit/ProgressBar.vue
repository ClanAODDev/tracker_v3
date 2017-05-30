<template>
    <div class="progress progress-small" style="margin: 0 0 20px">
        <div class="progress-bar progress-bar-accent active progress-bar-striped"
             role="progressbar" :aria-valuenow="progress"
             aria-valuemin="0" aria-valuemax="100" :style="{ width: progress + '%' }">
            <span class="sr-only">{{ progress }}% Complete</span>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['progress'],
    }
</script>