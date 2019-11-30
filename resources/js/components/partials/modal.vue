<template>
    <transition name="modal-fade">
        <div class="modal-backdrop">
            <div class="modal fade in" id="myModal" tabindex="-1"
                 style="display: block;"
                 role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header text-center">
                            <slot name="header">
                                I'm the default header!
                            </slot>
                        </div>
                        <div class="modal-body">
                            <slot name="body">
                                I'm the default body!
                            </slot>
                        </div>
                        <div class="modal-footer">
                            <slot name="footer">
                                I'm the default footer!
                            </slot>
                            <button type="button" class="btn btn-default"
                                    data-dismiss="modal" @click="close"
                                    aria-label="Close modal">Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>
<script>
  export default {
    name: 'modal',
    methods: {
      close () {
        this.$emit('close');
      },
    },
  };
</script>
<style>
    .modal-backdrop {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .modal-fade-enter,
    .modal-fade-leave-active {
        opacity: 0;
    }

    .modal-fade-enter-active,
    .modal-fade-leave-active {
        transition: opacity .5s ease
    }
</style>