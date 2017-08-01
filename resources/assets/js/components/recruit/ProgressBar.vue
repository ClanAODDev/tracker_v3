<template>
    <div class="progress progress-small" style="margin: 0 0 20px">
        <div class="progress-bar active progress-bar-striped"
             role="progressbar" :aria-valuenow="store.progress"
             aria-valuemin="0" aria-valuemax="100" :style="{ width: store.progress + '%', transition: 'width 2s' }">
            <span class="sr-only">{{ store.progress }}% Complete</span>
        </div>
    </div>
</template>

<script>
  import store from './store.js';

  export default {
    data () {
      return {
        store
      };
    }
  };
</script>