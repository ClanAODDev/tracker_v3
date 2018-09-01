<template>
    <form @submit.prevent="validateStep">

        <h3>Getting Started</h3>

        <p>If you haven't already brought your recruit into Teamspeak, you can share that information with them by visiting the teamspeak information page. Additionally, you can acquire the forum member id via the member list, sorted by join date.
        </p>

        <p>
            <a href="https://www.clanaod.net/forums/showthread.php?t=97502" target="_blank"
               class="btn btn-default">Teamspeak Information</a>
            <a class="btn btn-default m-l-sm" target="_blank"
               href="https://www.clanaod.net/forums/memberlist.php?order=desc&sort=joindate&pp=30">New Forum Members</a>
        </p>

        <p class="m-t-lg">Additionally, you should mention the clan-wide membership requirements as part of your recruitment:</p>

        <ul class="c-white">
            <li>Maintain minimum forum activity. Inactivity can result in removal from AOD</li>
            <li>Engage on TeamSpeak when playing a game AOD supports</li>
            <li>Strive to be a contributing member of your division</li>
            <li>Always be respectful of other clan members and leadership</li>
        </ul>

        <h3 class="m-t-xl"><i class="fa fa-address-card text-accent" aria-hidden="true"></i> Step 1: Member Data</h3>
        <hr />

        <div class="alert text-center">
            <i class="fa fa-question-circle text-accent"></i>
            Training, or just want to look around?
            <button type="button" name="trngMode" @click="toggleDemoMode" class="btn btn-rounded btn-default">
                <i class="fa text-success fa-check" v-show="store.inDemoMode"></i>
                <i class="fa text-danger fa-times" v-show="!store.inDemoMode"></i>
                Enable Demo Mode
            </button>
        </div>

        <div class="panel panel-filled">
            <div class="panel-heading">
                <strong class="text-uppercase">Information</strong>
            </div>
            <div class="panel-body">

                <p>Please be careful and ensure member information is entered accurately. Forum names can be changed when the member status request is submitted, and changed names will sync with the tracker automatically.</p>

                <div class="row">

                    <div class="col-md-4 form-group"
                         :class="{'input': true, 'has-warning': errors.has('member_id') }">
                        <label for="member_id">Forum Member Id</label>
                        <input type="number" class="form-control" name="member_id" v-model="store.member_id"
                               id="member_id" v-validate="'required|max:5'" @change="this.resetThreadsOnIdChange"
                               :disabled="store.inDemoMode" />
                        <span v-show="errors.has('member_id')"
                              class="help-block">{{ errors.first('member_id') }}</span>
                    </div>

                    <div class="col-md-4 form-group"
                         :class="{'input': true, 'has-warning': errors.has('forum_name') }">
                        <label for="forum_name">Desired Forum Name <span class="text-info">*</span></label>
                        <input type="text" class="form-control" name="forum_name" v-model="store.forum_name"
                               id="forum_name" v-validate="{ required: true, regex: /^((?!AOD_|aod_).)*$/}"
                               :disabled="store.inDemoMode" />
                        <span v-show="errors.has('forum_name')"
                              class="help-block">{{ errors.first('forum_name') }}</span>
                    </div>

                    <div class="col-md-4 form-group"
                         :class="{'input': true, 'has-warning': errors.has('ingame_name') }">
                        <label for="ingame_name">{{ store.handleName }}</label>
                        <input type="text" class="form-control" name="ingame_name" v-model="store.ingame_name"
                               id="ingame_name" :disabled="store.inDemoMode" />
                    </div>

                    <div class="col-md-4 form-group"
                         :class="{'input': true, 'has-warning': errors.has('rank') }">
                        <label for="rank">Rank</label>
                        <select name="rank" id="rank" class="form-control" v-model="store.rank"
                        v-validate="{ required: true }">
                            <option :value="id"
                                    :selected="id === 1 ? 'selected' : null"
                                    v-for="(rank,id) in this.ranks"
                            >{{ rank }}</option>
                        </select>
                        <span v-show="errors.has('rank')"
                              class="help-block">{{ errors.first('rank') }}</span>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <p class="text-info">* Provide the forum name the member wishes to use. If it is different than the member's current name, it will be updated to match.</p>
            </div>
        </div>

        <div class="m-t-xl">
            <div class="panel panel-filled" v-if="store.division.platoons">
                <div class="panel-heading text-uppercase">
                    <strong>Assignment</strong>
                </div>
                <div class="panel-body">
                    <p>Depending on your division's configuration, a {{ store.locality.platoon }} and {{ store.locality.squad }} assignment may be required.</p>

                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label for="platoon">{{ store.locality.platoons }}</label>
                            <select name="platoon" id="platoon" class="form-control" v-model="store.platoon"
                                    @change="store.getPlatoonSquads(store.platoon)">
                                <option value="">Select a platoon...</option>
                                <option :value="id" v-for="(name, id, index) in store.division.platoons">
                                    {{ name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-sm-6 form-group">
                            <label for="squad">{{ store.locality.squads }}</label>
                            <select name="squad" id="squad" class="form-control" v-model="store.squad"
                                    :disabled="! store.division.squads.length">
                                <option value="" v-if="! store.division.squads.length" selected>No squads available
                                </option>
                                <option value="" selected v-else>Select a squad...</option>
                                <option :value="squad.id" v-for="squad in store.division.squads">
                                    {{ (squad.name) ? squad.name : 'Squad #' + squad.id }} - {{ (squad.leader) ? squad.leader.name : 'TBA' }}
                                    ({{ squad.members.length }} members)
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-filled panel-c-danger" v-else>
                <div class="panel-heading text-uppercase">
                    <strong>Assignment</strong>
                </div>
                <div class="panel-body">
                    <p>
                        <i class="fa fa-exclamation-triangle text-danger"></i> Your division has no {{ store.locality.platoons }}, so assignment is not unavailable. A division leader will need to create one.
                    </p>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success pull-right">
            Continue <i class="fa fa-arrow-right"></i>
        </button>
    </form>
</template>

<script>
  import store from './store.js';
  import toastr from 'toastr';
  import ProgressBar from './ProgressBar.vue';

  export default {

    props: ['ranks'],

    methods: {
      validateStep: function () {
        this.$validator.validateAll().then((result) => {
          if (!result) {
            toastr.error('Something is wrong with your member information', 'Uh oh...');
            return false;
          }
          store.getDivisionThreads(store.division.abbreviation);
          store.currentStep = 'step-two';
          store.progress = 50;
        }).catch(() => {
          toastr.error('Something is wrong with your member information', 'Uh oh...');
          return false;
        });
      },

      resetThreadsOnIdChange: () => {
        store.division.threads = [];
      },

      forumNameToIngameName: () => {
        store.ingame_name = store.forum_name;
      },

      /**
       * Provide dummy data for training
       */
      toggleDemoMode: () => {
        store.inDemoMode = !store.inDemoMode;
        store.member_id = 99999;
        store.forum_name = 'test-user';
        store.ingame_name = 'test-user';

        if (store.inDemoMode) {
          toastr.success('Demo mode enabled!', 'Success!');
        }
      },
    },

    components: {
      'progress-bar': ProgressBar
    },

    data: function () {
      return {
        store
      };
    },
  };

</script>