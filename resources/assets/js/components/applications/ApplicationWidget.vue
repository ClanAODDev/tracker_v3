<template>
  <div class="application-widget">
    <div class="actions-bar" v-if="store.currentView !== 'list'">
      <button class="btn btn-default btn-sm" @click="store.setView('list')">
        <i class="fa fa-arrow-left"></i> Back
      </button>
    </div>

    <transition name="fade" mode="out-in">
      <application-list
        v-if="store.currentView === 'list'"
        key="list"
      />
      <application-detail
        v-else-if="store.currentView === 'detail'"
        key="detail"
      />
    </transition>
  </div>
</template>

<script>
import store from './store.js';
import ApplicationList from './ApplicationList.vue';
import ApplicationDetail from './ApplicationDetail.vue';

export default {
  components: {
    ApplicationList,
    ApplicationDetail,
  },

  data() {
    return { store };
  },
};
</script>

<style scoped>
.application-widget {
  padding: 5px 0;
}

.actions-bar {
  margin-bottom: 15px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
