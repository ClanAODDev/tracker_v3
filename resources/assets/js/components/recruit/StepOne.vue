<template>
  <form @submit.prevent="submitForm" class="recruit-form">

    <div class="recruit-form-header">
      <h3>Add New Recruit</h3>
      <button type="button" class="btn btn-sm" :class="store.inDemoMode ? 'btn-success' : 'btn-default'" @click="toggleDemoMode">
        <i class="fa" :class="store.inDemoMode ? 'fa-check' : 'fa-flask'"></i>
        Demo Mode
      </button>
    </div>

    <div v-if="store.inDemoMode" class="demo-notice">
      <i class="fa fa-info-circle"></i>
      Demo mode is active. No data will be saved.
    </div>

    <div v-if="store.loading.divisionData" class="recruit-loading">
      <i class="fa fa-spinner fa-spin"></i> Loading division data...
    </div>

    <div v-else-if="store.errors.divisionData" class="recruit-error">
      <i class="fa fa-exclamation-triangle"></i>
      {{ store.errors.divisionData }}
      <button type="button" class="btn btn-sm btn-default" @click="retryLoad">Retry</button>
    </div>

    <template v-else>
      <!-- Member Verification Section -->
      <div class="recruit-section">
        <div class="recruit-section-header">
          <i class="fa fa-user-check"></i> Member Verification
        </div>
        <div class="recruit-section-body">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group" :class="memberIdValidationClass">
                <label for="member_id">Forum Member ID</label>
                <div class="input-with-status">
                  <input type="number" class="form-control" id="member_id"
                         v-model="store.member.id"
                         @input="onMemberIdChange"
                         :disabled="store.inDemoMode"
                         placeholder="e.g. 12345" />
                  <span class="input-status" v-if="store.member.id">
                    <i class="fa fa-spinner fa-spin" v-if="store.validation.loading"></i>
                    <i class="fa fa-check text-success" v-else-if="store.validation.memberId.valid && store.validation.memberId.verifiedEmail"></i>
                    <i class="fa fa-times text-danger" v-else-if="store.member.id.length > 0"></i>
                  </span>
                </div>
                <span class="help-block text-danger" v-if="store.member.id && !store.validation.memberId.valid && !store.validation.loading">
                  Member ID not found on forums
                </span>
                <span class="help-block text-warning" v-else-if="store.member.id && store.validation.memberId.valid && !store.validation.memberId.verifiedEmail && !store.validation.loading">
                  Member has not verified their email
                </span>
              </div>
            </div>
            <div class="col-md-8" v-if="store.validation.memberId.currentUsername">
              <div class="form-group">
                <label>Current Forum Username</label>
                <div class="current-username">
                  <i class="fa fa-user"></i> {{ store.validation.memberId.currentUsername }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recruit Details Section -->
      <div class="recruit-section">
        <div class="recruit-section-header">
          <i class="fa fa-id-card"></i> Recruit Details
        </div>
        <div class="recruit-section-body">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group" :class="forumNameValidationClass">
                <label for="forum_name">Forum Name</label>
                <div class="input-with-status">
                  <input type="text" class="form-control" id="forum_name"
                         v-model="store.member.forum_name"
                         @input="onForumNameChange"
                         :disabled="store.inDemoMode"
                         placeholder="Desired forum name" />
                  <span class="input-status" v-if="store.member.forum_name">
                    <i class="fa fa-spinner fa-spin" v-if="store.validation.loading"></i>
                    <i class="fa fa-check text-success" v-else-if="store.validation.forumName.valid"></i>
                    <i class="fa fa-times text-danger" v-else></i>
                  </span>
                </div>
                <span class="help-block" v-if="store.member.forum_name && store.validation.forumName.valid">
                  Will become: <strong>{{ store.getFormattedName() }}</strong>
                </span>
                <span class="help-block text-danger" v-else-if="store.member.forum_name && store.member.forum_name.toLowerCase().startsWith('aod_')">
                  Do not include "AOD_" prefix
                </span>
                <span class="help-block text-danger" v-else-if="store.member.forum_name && !store.validation.forumName.available && !store.validation.loading">
                  This name is already taken
                </span>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="ingame_name">In-Game Handle <span class="text-muted">(optional)</span></label>
                <input type="text" class="form-control" id="ingame_name"
                       v-model="store.member.ingame_name"
                       :disabled="store.inDemoMode"
                       placeholder="In-game name" />
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group" :class="{ 'has-error': !store.member.rank && submitted }">
                <label for="rank">Rank</label>
                <select id="rank" class="form-control" v-model="store.member.rank">
                  <option value="">Select rank...</option>
                  <option v-for="(name, id) in ranks" :key="id" :value="id">{{ name }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Assignment Section -->
      <div class="recruit-section" v-if="store.division.platoons.length">
        <div class="recruit-section-header">
          <i class="fa fa-users"></i> Assignment
        </div>
        <div class="recruit-section-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="platoon">{{ store.division.locality.platoon }}</label>
                <select id="platoon" class="form-control" v-model="store.member.platoon" @change="onPlatoonChange">
                  <option value="">Select {{ store.division.locality.platoon.toLowerCase() }}...</option>
                  <option v-for="platoon in store.division.platoons" :key="platoon.id" :value="platoon.id">
                    {{ platoon.name }}
                  </option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="squad">{{ store.division.locality.squad }} <span class="text-muted">(optional)</span></label>
                <select id="squad" class="form-control" v-model="store.member.squad" :disabled="!selectedPlatoonSquads.length">
                  <option value="">{{ selectedPlatoonSquads.length ? 'Select ' + store.division.locality.squad.toLowerCase() + '...' : 'No ' + store.division.locality.squad.toLowerCase() + 's available' }}</option>
                  <option v-for="squad in selectedPlatoonSquads" :key="squad.id" :value="squad.id">
                    {{ squad.name || 'Squad #' + squad.id }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Agreements Section (Collapsible) -->
      <div class="recruit-section recruit-section-collapsible" v-if="store.division.threads.length">
        <div class="recruit-section-header" @click="toggleSection('agreements')">
          <i class="fa fa-file-contract"></i> Agreements
          <span class="section-toggle">
            <i class="fa" :class="sections.agreements ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
          </span>
        </div>
        <div class="recruit-section-body" v-show="sections.agreements">
          <p class="text-muted">Confirm the recruit has read the following:</p>
          <div class="agreement-list">
            <div v-for="(thread, index) in store.division.threads" :key="index" class="agreement-item">
              <label class="agreement-item-content">
                <input type="checkbox" v-model="thread.read" />
                <span class="agreement-name">
                  <a :href="getThreadUrl(thread.id)" target="_blank">
                    {{ thread.name }}
                  </a>
                </span>
                <span class="agreement-comment text-muted" v-if="thread.comments">{{ thread.comments }}</span>
              </label>
              <button type="button" class="btn btn-xs btn-default agreement-copy" @click="copyThreadUrl(thread.id)" title="Copy URL">
                <i class="fa fa-clone"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Tasks Section (Collapsible) -->
      <div class="recruit-section recruit-section-collapsible" v-if="store.division.tasks.length">
        <div class="recruit-section-header" @click="toggleSection('tasks')">
          <i class="fa fa-tasks"></i> In-Processing Tasks
          <span class="section-toggle">
            <i class="fa" :class="sections.tasks ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
          </span>
        </div>
        <div class="recruit-section-body" v-show="sections.tasks">
          <p class="text-muted">Checklist for onboarding the new recruit:</p>
          <div class="task-list">
            <label v-for="(task, index) in store.division.tasks" :key="index" class="task-item">
              <input type="checkbox" v-model="task.complete" />
              <span class="task-description" v-html="linkifyUrls(task.description)"></span>
            </label>
          </div>
        </div>
      </div>

      <!-- Error Display -->
      <div class="recruit-error" v-if="store.errors.submission">
        <i class="fa fa-exclamation-triangle"></i>
        {{ store.errors.submission }}
      </div>

      <!-- Form Actions -->
      <div class="recruit-form-actions">
        <a :href="cancelUrl" class="btn btn-default">Cancel</a>
        <button type="submit" class="btn btn-success" :disabled="!store.isFormValid() || store.submitting">
          <span v-if="store.submitting">
            <i class="fa fa-spinner fa-spin"></i> Adding Recruit...
          </span>
          <span v-else>
            Add Recruit <i class="fa fa-arrow-right"></i>
          </span>
        </button>
      </div>
    </template>
  </form>
</template>

<script>
import store from './store.js';

export default {
  props: ['ranks', 'recruiterId', 'divisionSlug', 'cancelUrl'],

  data() {
    return {
      store,
      submitted: false,
      sections: {
        agreements: false,
        tasks: false,
      },
    };
  },

  computed: {
    memberIdValidationClass() {
      if (!store.member.id) return {};
      if (store.validation.loading) return {};
      if (store.validation.memberId.valid && store.validation.memberId.verifiedEmail) {
        return { 'has-success': true };
      }
      return { 'has-error': true };
    },

    forumNameValidationClass() {
      if (!store.member.forum_name) return {};
      if (store.validation.loading) return {};
      if (store.validation.forumName.valid) {
        return { 'has-success': true };
      }
      return { 'has-error': true };
    },

    selectedPlatoonSquads() {
      return store.getSquadsForPlatoon(store.member.platoon);
    },

    isNewMember() {
      return store.validation.memberId.valid && !store.validation.memberId.existsInTracker;
    },
  },

  watch: {
    isNewMember(isNew) {
      if (isNew) {
        this.sections.agreements = true;
        this.sections.tasks = true;
      }
    },
  },

  methods: {
    toggleDemoMode() {
      store.toggleDemoMode();
    },

    toggleSection(section) {
      this.sections[section] = !this.sections[section];
    },

    onMemberIdChange() {
      store.validateMemberId(store.member.id);
    },

    onForumNameChange() {
      store.validateForumName(store.member.forum_name, store.member.id);
    },

    onPlatoonChange() {
      store.member.squad = '';
    },

    retryLoad() {
      store.loadDivisionData(this.divisionSlug);
    },

    getThreadUrl(threadId) {
      return `https://www.clanaod.net/forums/showthread.php?t=${threadId}`;
    },

    copyThreadUrl(threadId) {
      const url = this.getThreadUrl(threadId);
      navigator.clipboard.writeText(url).then(() => {
        toastr.success('URL copied to clipboard');
      });
    },

    linkifyUrls(text) {
      if (!text) return '';
      const urlPattern = /(https?:\/\/[^\s<]+)/g;
      return text.replace(urlPattern, '<a href="$1" target="_blank">$1</a>');
    },

    submitForm() {
      this.submitted = true;

      if (!store.isFormValid()) {
        return;
      }

      store.submitRecruitment().catch(() => {
        // Error is handled in store
      });
    },
  },

  mounted() {
    store.recruiter_id = this.recruiterId;
    store.loadDivisionData(this.divisionSlug);
  },
};
</script>
