<template>
    <div>
        <h3 class="m-t-xl"><i class="fa fa-envelope-o text-accent"></i> Send Welcome PM</h3>

        <p>A welcome PM has been prepared to send to your new recruit. Simply copy the message below, and click the button below to open a private message to submit to your new recruit.</p>

        <textarea name="welcome_pm" id="welcome_pm" class="form-control"
                  rows="5">{{ store.division.settings.welcome_pm }}</textarea>

        <div class="text-center p-lg bs-example">
            <button data-clipboard-target="#welcome_pm" class="copy-to-clipboard btn-success btn"><i
                    class="fa fa-clone"></i> Copy Contents
            </button>
            <a :href="'https://clanaod.net/forums/private.php?do=newpm&u=' + store.member_id"
               target="_blank" class="btn btn-accent">
                <i class="fa fa-external-link text-accent" aria-hidden="true"></i> Send PM
            </a>
        </div>

    </div>
</template>

<script>
  import store from './store.js';

  export default {
    data: function () {
      return {
        store
      };
    },
  };
</script>