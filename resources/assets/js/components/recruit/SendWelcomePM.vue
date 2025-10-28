<template>
    <div>
        <h4 class="m-t-xl"><i class="fa fa-envelope-o text-accent"></i> Send Welcome DM</h4>

        <p>A welcome DM has been prepared to send to your new recruit. Copy the message below and either use the
          button to send a forum PM, or you can send the message directly via Discord.</p>

        <textarea name="welcome_pm" id="welcome_pm" class="form-control"
                  rows="5">{{ formulateWelcomePM() }}</textarea>

        <div class="text-center p-lg bs-example">
            <button data-clipboard-target="#welcome_pm" class="copy-to-clipboard btn-success btn"><i
                    class="fa fa-clone"></i> Copy Contents
            </button>
            <a :href="'https://clanaod.net/forums/private.php?do=newpm&u=' + store.member_id"
               target="_blank" class="btn btn-accent">
                <i class="fa fa-external-link text-accent" aria-hidden="true"></i> Send Forum PM
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

    methods: {
      formulateWelcomePM: function () {
        var message =
            (store && store.division.settings && store.division.settings.welcome_pm) || "";
        if (!message) return "";

        var replacements = {
          name: (store && store.forum_name) || "",
          ingame_name: (store && store.ingame_name) || "",
        };

        var formatted = message;
        Object.keys(replacements).forEach(function (key) {
          var val = replacements[key] == null ? "" : String(replacements[key]);
          var re = new RegExp('{{\\s*' + key + '\\s*}}', 'g');
          formatted = formatted.replace(re, val);
        });

        return formatted;
      }
    }
  };
</script>