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
      <span class="themed-spinner"></span> Loading division data...
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
          <StepIndicator :step="1" :complete="isMemberVerificationComplete" />
        </div>
        <div class="recruit-section-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group" :class="memberIdValidationClass">
                <label for="member_id">Forum Member ID <span class="text-danger" v-if="!store.selectedPendingUser">*</span></label>
                <div class="input-with-status">
                  <input type="number" class="form-control" id="member_id"
                         v-model="store.member.id"
                         @input="onMemberIdChange"
                         :disabled="store.inDemoMode || store.selectedPendingUser"
                         :placeholder="store.selectedPendingUser ? 'Will be created' : 'e.g. 12345'" />
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
                  <template v-if="store.validation.memberId.groupId === 3">
                    Member needs to validate their email address before they can be recruited.
                  </template>
                  <template v-else-if="store.validation.memberId.groupId === 4">
                    Member needs to complete the AuthLink process before they can be recruited.
                  </template>
                  <template v-else>
                    Member is not in the <code>Registered Users</code> group.
                  </template>
                </span>
                <span class="help-block text-muted" v-else-if="store.selectedPendingUser">
                  <i class="fab fa-discord" style="color: #5865F2;"></i> Forum account will be created for <strong>{{ store.selectedPendingUser.discord_username }}</strong>
                  <a href="#" @click.prevent="clearPendingUser" class="text-muted" style="margin-left: 0.5rem;"><i class="fa fa-times"></i></a>
                </span>
              </div>
            </div>
            <div class="col-md-6" v-if="store.division.pending_discord.length && !store.validation.memberId.currentUsername">
              <div class="form-group">
                <label for="pending_user">
                  <i class="fab fa-discord" style="color: #5865F2;"></i> Pending Discord Registrations
                </label>
                <select id="pending_user" class="form-control" @change="onPendingUserSelect" :disabled="store.inDemoMode">
                  <option value="">Select from pending...</option>
                  <option v-for="user in store.division.pending_discord" :key="user.id" :value="user.id">
                    {{ user.discord_username }} ({{ user.created_at }})
                  </option>
                </select>
              </div>
            </div>
            <div class="col-md-6" v-else-if="store.validation.memberId.currentUsername">
              <div class="form-group">
                <label>Current Forum Username</label>
                <div class="current-username">
                  <i class="fa fa-user"></i> {{ store.validation.memberId.currentUsername }}
                  <span v-if="store.validation.memberId.existsInTracker" class="existing-member-badge">
                    <i class="fa fa-history"></i> Previous Member
                  </span>
                </div>
                <div class="member-tags-display" v-if="store.validation.memberId.tags.length">
                  <span v-for="(tag, index) in store.validation.memberId.tags" :key="index" class="badge badge-tag" :title="tag.division">
                    {{ tag.name }}
                  </span>
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
          <StepIndicator :step="2" :complete="isRecruitDetailsComplete" />
        </div>
        <div class="recruit-section-body">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group" :class="forumNameValidationClass">
                <label for="forum_name">Forum Name <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="text" class="form-control" id="forum_name"
                         v-model="store.member.forum_name"
                         @input="onForumNameChange"
                         :disabled="store.inDemoMode"
                         placeholder="Desired forum name" />
                  <span class="input-group-addon input-group-status" v-if="store.member.forum_name">
                    <i class="fa fa-spinner fa-spin" v-if="store.validation.loading"></i>
                    <i class="fa fa-check text-success" v-else-if="store.validation.forumName.valid"></i>
                    <i class="fa fa-times text-danger" v-else></i>
                  </span>
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-default" @click="copyForumNameToHandle" :disabled="!store.member.forum_name || store.inDemoMode" title="Copy to in-game handle">
                      <i class="fa fa-arrow-right"></i>
                    </button>
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
              <div class="form-group" :class="{ 'has-error': !store.member.ingame_name && submitted }">
                <label for="ingame_name">In-Game Handle <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="ingame_name"
                       v-model="store.member.ingame_name"
                       :disabled="store.inDemoMode"
                       placeholder="In-game name" />
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group" :class="{ 'has-error': !store.member.rank && submitted }">
                <label for="rank">Rank <span class="text-danger">*</span></label>
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
          <StepIndicator :step="3" :complete="isAssignmentComplete" />
        </div>
        <div class="recruit-section-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group" :class="{ 'has-error': !store.member.platoon && submitted }">
                <label for="platoon">{{ store.division.locality.platoon }} <span class="text-danger">*</span></label>
                <select id="platoon" class="form-control" v-model="store.member.platoon" @change="onPlatoonChange">
                  <option value="">Select {{ store.division.locality.platoon.toLowerCase() }}...</option>
                  <option v-for="platoon in store.division.platoons" :key="platoon.id" :value="platoon.id">
                    {{ platoon.name }} ({{ platoon.members_count }}){{ platoon.leader_name ? ' - ' + platoon.leader_name : '' }}
                  </option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group" :class="{ 'has-error': !store.member.squad && selectedPlatoonSquads.length && submitted }">
                <label for="squad">{{ store.division.locality.squad }} <span class="text-danger" v-if="selectedPlatoonSquads.length">*</span></label>
                <select id="squad" class="form-control" v-model="store.member.squad" :disabled="!selectedPlatoonSquads.length">
                  <option value="">{{ selectedPlatoonSquads.length ? 'Select ' + store.division.locality.squad.toLowerCase() + '...' : 'No ' + store.division.locality.squad.toLowerCase() + 's available' }}</option>
                  <option v-for="squad in selectedPlatoonSquads" :key="squad.id" :value="squad.id">
                    {{ squad.name || 'Squad #' + squad.id }} ({{ squad.members_count }}){{ squad.leader_name ? ' - ' + squad.leader_name : '' }}
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
          <StepIndicator :step="4" :complete="isAgreementsComplete" :visible="sections.agreements" />
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
                <div class="agreement-details">
                  <span class="agreement-name">
                    <a :href="thread.url" target="_blank">
                      {{ thread.name }}
                    </a>
                  </span>
                  <span class="agreement-comment text-muted" v-if="thread.comments">{{ thread.comments }}</span>
                </div>
              </label>
              <button type="button" class="btn btn-xs btn-default agreement-copy" @click="copyThreadUrl(thread)" title="Copy URL">
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
          <StepIndicator :step="5" :complete="isTasksComplete" :visible="sections.tasks" />
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
            <span class="themed-spinner spinner-sm"></span> Adding Recruit...
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
import StepIndicator from './StepIndicator.vue';

export default {
  components: { StepIndicator },
  props: ['ranks', 'rankLabels', 'recruiterId', 'divisionSlug', 'cancelUrl'],

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

    isMemberVerificationComplete() {
      if (store.selectedPendingUser) return true;
      return store.validation.memberId.valid && store.validation.memberId.verifiedEmail;
    },

    isRecruitDetailsComplete() {
      return !!(store.member.forum_name &&
             store.validation.forumName.valid &&
             store.member.ingame_name &&
             store.member.rank);
    },

    isAssignmentComplete() {
      if (store.loading.divisionData) return false;
      if (!store.division.platoons.length) return true;
      const hasSquads = this.selectedPlatoonSquads.length > 0;
      return !!(store.member.platoon && (!hasSquads || store.member.squad));
    },

    isAgreementsComplete() {
      if (store.loading.divisionData) return false;
      if (!store.division.threads.length) return true;
      return store.division.threads.every(t => t.read);
    },

    isTasksComplete() {
      if (store.loading.divisionData) return false;
      if (!store.division.tasks.length) return true;
      return store.division.tasks.every(t => t.complete);
    },
  },

  watch: {
    isNewMember(isNew) {
      if (isNew) {
        this.sections.agreements = true;
        this.sections.tasks = true;
      }
    },
    'store.selectedPendingUser'(user) {
      if (user) {
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

    onPendingUserSelect(event) {
      store.selectPendingUser(event.target.value);
    },

    clearPendingUser() {
      store.clearPendingUser();
      store.member.forum_name = '';
      store.member.rank = '';
      document.getElementById('pending_user').value = '';
    },

    retryLoad() {
      store.loadDivisionData(this.divisionSlug);
    },

    copyThreadUrl(thread) {
      navigator.clipboard.writeText(thread.url).then(() => {
        thread.read = true;
        toastr.success('URL copied to clipboard');
      });
    },

    copyForumNameToHandle() {
      if (store.member.forum_name) {
        store.member.ingame_name = store.member.forum_name;
      }
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
    store.rankLabels = this.rankLabels;
    store.loadDivisionData(this.divisionSlug);
  },
};
</script>
