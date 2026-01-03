<template>
  <span class="step-indicator" :class="{ complete: complete }" v-if="visible">
    <i class="fa fa-check" v-if="complete"></i>
    <span v-else>{{ step }}</span>
  </span>
</template>

<script>
export default {
  name: 'StepIndicator',
  props: {
    step: {
      type: Number,
      required: true
    },
    complete: {
      type: Boolean,
      default: false
    },
    visible: {
      type: Boolean,
      default: true
    }
  }
}
</script>
