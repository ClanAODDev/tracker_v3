<template>
  <div class="recruit-confirmation">
    <div class="recruit-success">
      <div class="recruit-success-icon">
        <i class="fa fa-check-circle"></i>
      </div>
      <h3>Recruit Added Successfully</h3>
      <p class="recruit-success-details">
        <strong>{{ store.getFormattedName() }}</strong> has been added to the division.
        <template v-if="assignmentText">
          <br />Assigned to: {{ assignmentText }}
        </template>
      </p>
    </div>

    <div v-if="store.inDemoMode" class="demo-notice">
      <i class="fa fa-info-circle"></i>
      Demo mode was active. No data was saved.
    </div>

    <div class="recruit-section" v-if="showHousekeeping">
      <div class="recruit-section-header">
        <i class="fa fa-clipboard-check"></i> Housekeeping
      </div>
      <div class="recruit-section-body">
        <p class="text-muted">Optional tasks to complete for your new recruit:</p>

        <div class="housekeeping-actions">
          <div class="housekeeping-action" v-if="store.division.welcome_area">
            <div class="housekeeping-action-icon">
              <i class="fa fa-reply-all"></i>
            </div>
            <div class="housekeeping-action-content">
              <h5>Create Welcome Post</h5>
              <p v-if="store.division.use_welcome_thread">
                Create a post in the division's welcome thread to introduce your recruit.
              </p>
              <p v-else>
                Create a thread in the division's welcome forum to introduce your recruit.
              </p>
              <a :href="welcomePostUrl" target="_blank" class="btn btn-sm btn-accent">
                <i class="fa fa-external-link"></i>
                {{ store.division.use_welcome_thread ? 'Create Post' : 'Create Thread' }}
              </a>
            </div>
          </div>

          <div class="housekeeping-action" v-if="store.division.welcome_pm">
            <div class="housekeeping-action-icon">
              <i class="fa fa-envelope"></i>
            </div>
            <div class="housekeeping-action-content">
              <h5>Send Welcome DM</h5>
              <p>Send a welcome message to your recruit via forum PM or Discord.</p>
              <div class="welcome-pm-container">
                <textarea id="welcome_pm" class="form-control" rows="4" readonly>{{ formattedWelcomePM }}</textarea>
                <div class="welcome-pm-actions">
                  <button type="button" class="btn btn-sm btn-success" @click="copyWelcomePM">
                    <i class="fa fa-clone"></i> Copy
                  </button>
                  <a :href="forumPMUrl" target="_blank" class="btn btn-sm btn-accent">
                    <i class="fa fa-external-link"></i> Send Forum PM
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="recruit-form-actions">
      <button type="button" class="btn btn-default" @click="addAnother">
        <i class="fa fa-plus"></i> Add Another Recruit
      </button>
      <a :href="divisionUrl" class="btn btn-success">
        <i class="fa fa-arrow-left"></i> Back to Division
      </a>
    </div>
  </div>
</template>

<script>
import store from './store.js';

export default {
  props: ['divisionSlug'],

  data() {
    return {
      store,
    };
  },

  computed: {
    assignmentText() {
      const parts = [];
      if (store.member.platoon) {
        const platoon = store.division.platoons.find(p => p.id === parseInt(store.member.platoon));
        if (platoon) {
          parts.push(platoon.name);
          if (store.member.squad) {
            const squad = platoon.squads.find(s => s.id === parseInt(store.member.squad));
            if (squad && squad.name) {
              parts.push(squad.name);
            }
          }
        }
      }
      return parts.join(' > ');
    },

    showHousekeeping() {
      return store.division.welcome_area || store.division.welcome_pm;
    },

    welcomePostUrl() {
      if (store.division.use_welcome_thread) {
        return `https://www.clanaod.net/forums/newreply.php?do=newreply&t=${store.division.welcome_area}`;
      }
      return `https://www.clanaod.net/forums/newthread.php?do=newthread&f=${store.division.welcome_area}`;
    },

    forumPMUrl() {
      return `https://clanaod.net/forums/private.php?do=newpm&u=${store.member.id}`;
    },

    formattedWelcomePM() {
      let message = store.division.welcome_pm || '';
      if (!message) return '';

      const replacements = {
        name: store.member.forum_name || '',
        ingame_name: store.member.ingame_name || '',
      };

      Object.keys(replacements).forEach(key => {
        const val = replacements[key] == null ? '' : String(replacements[key]);
        const re = new RegExp('{{\\s*' + key + '\\s*}}', 'g');
        message = message.replace(re, val);
      });

      return message;
    },

    divisionUrl() {
      return `${store.base_url}/divisions/${this.divisionSlug}`;
    },
  },

  methods: {
    addAnother() {
      store.resetForNewRecruit();
    },

    copyWelcomePM() {
      navigator.clipboard.writeText(this.formattedWelcomePM).then(() => {
        toastr.success('Copied!');
      });
    },
  },
};
</script>
