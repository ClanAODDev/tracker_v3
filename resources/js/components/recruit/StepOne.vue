<template>
    <form @submit.prevent="validateStep">

        <h3>Getting Started</h3>

        <p>If you haven't already brought your recruit into Teamspeak, you can share that information with them by
            visiting the teamspeak information page. Additionally, you can acquire the forum member id via the member
            list, sorted by join date.
        </p>

        <p>
            <a href="https://www.clanaod.net/forums/showthread.php?t=97502" target="_blank"
               class="btn btn-default">Teamspeak Info</a>
            <a class="btn btn-default m-l-sm" target="_blank"
               href="https://www.clanaod.net/forums/showthread.php?t=208233">Discord Info</a>
            <a class="btn btn-default m-l-sm" target="_blank"
               href="https://www.clanaod.net/forums/memberlist.php?order=desc&sort=joindate&pp=30">New Forum Members</a>
        </p>

        <p class="m-t-lg">Additionally, you should mention the clan-wide membership requirements as part of your
            recruitment:</p>

        <ul class="c-white">
            <li>Maintain minimum forum activity. Inactivity can result in removal from AOD</li>
            <li>Engage on TeamSpeak when playing a game AOD supports</li>
            <li>Strive to be a contributing member of your division</li>
            <li>Always be respectful of other clan members and leadership</li>
        </ul>

        <h3 class="m-t-xl"><i class="fa fa-address-card text-accent" aria-hidden="true"></i> Step 1: Member Data</h3>
        <hr/>

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

                <p>Please be careful and ensure member information is entered accurately. Forum names can be changed
                    when the member status request is submitted, and changed names will sync with the tracker
                    automatically.</p>

                <div class="row">

                    <div class="col-md-4 form-group"
                         :class="{'input': true, 'has-warning': !store.validMemberId || !store.verifiedEmail }">
                        <label for="member_id">Forum Member Id</label>
                        <input type="number" class="form-control" name="member_id"
                               v-model="store.member_id"
                               id="member_id" v-validate="'required|max:5'"
                               :disabled="store.inDemoMode"
                               @blur="validateMemberId"/>
                    </div>

                    <div class="col-md-4 form-group"
                         :class="{'input': true, 'has-warning': !store.nameDoesNotExist || errors.has('forum_name') }">
                        <label for="forum_name">Desired Forum Name</label>
                        <input type="text" class="form-control" name="forum_name" v-model="store.forum_name"
                               id="forum_name" v-validate="{ required: true, regex: /^[\w\s.-]+$/}"
                               @blur="validateMemberDoesNotExist"
                               :disabled="store.inDemoMode"/>
                    </div>

                    <div class="col-md-4 form-group"
                         :class="{'input': true, 'has-warning': errors.has('ingame_name') }">
                        <label for="ingame_name">{{ store.handleName }}</label>
                        <input type="text" class="form-control" name="ingame_name" v-model="store.ingame_name"
                               id="ingame_name" :disabled="store.inDemoMode"/>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4 form-group"
                         :class="{'input': true, 'has-warning': errors.has('rank') }">
                        <label for="rank">Rank</label>
                        <select name="rank" id="rank" class="form-control" v-model="store.rank"
                                v-validate="{ required: true }">
                            <option :value="id"
                                    :selected="id === 1 ? 'selected' : null"
                                    v-for="(rank,id) in this.ranks"
                            >{{ rank }}
                            </option>
                        </select>
                        <span v-show="errors.has('rank')"
                              class="help-block">{{ errors.first('rank') }}</span>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                   <span v-show="store.validating">
                       <span class="small">VALIDATING</span> <i class="fa fa-cog fa-spin fa-fw"></i>
                   </span>
                <div class="alert alert-danger" v-if="errors.any()">
                    <ul>
                        <li v-for="error in errors">
                            {{ error.msg }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>


        <div class="m-t-xl">
            <div class="panel panel-filled" v-if="store.division.platoons">
                <div class="panel-heading text-uppercase">
                    <strong>Assignment</strong>
                </div>
                <div class="panel-body">
                    <p>Depending on your division's configuration, a {{ store.locality.platoon }} and {{
                        store.locality.squad }} assignment may be required.</p>

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
                                    {{ (squad.name) ? squad.name : 'Squad #' + squad.id }} - {{ (squad.leader) ?
                                    squad.leader.name : 'TBA' }}
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
                        <i class="fa fa-exclamation-triangle text-danger"></i> Your division has no {{
                        store.locality.platoons }}, so assignment is not unavailable. A division leader will need to
                        create one.
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
    import {focus} from 'vue-focus';

    export default {
        directives: {focus: focus},
        props: ['ranks'],

        methods: {
            validateStep: function () {

                this.$validator.validateAll().then((result) => {

                    if (!store.inDemoMode) {
                        if (!store.validMemberId) {
                            toastr.error('Oops, your member id appears to be invalid!');
                            return false;
                        }

                        if (!store.verifiedEmail) {
                            toastr.error('Member has not yet verified their email and cannot be processed.', true);
                            return false;
                        }

                        if (!store.nameDoesNotExist) {
                            toastr.error('That forum name appears to already be taken.');
                            return false;
                        }
                    }

                    if (!result) {
                        toastr.error('Something is wrong with your member information', 'Uh oh...');
                        return false;
                    }

                    store.getDivisionThreads(store.division.abbreviation);
                    store.currentStep = 'step-two';
                    store.progress = 50;
                }).catch(() => {
                    toastr.error('Something is wrong with your member information', 'Error');
                    return false;
                });

            },

            validateMemberDoesNotExist: function () {

                if (store.forum_name.includes('AOD_') || store.forum_name.includes('aod_')) {
                    this.errors.add({
                        field: 'forum_name_aod',
                        msg: 'Do not include "AOD_" in the desired forum name'
                    });
                } else {
                    this.errors.remove('forum_name_aod');
                }

                // don't attempt to query a badly formatted name
                if (store.forum_name && store.member_id && !this.errors.has('forum_name')) {
                    store.validating = true;
                    axios.post(window.Laravel.appPath + '/validate-name/', {
                        name: store.forum_name.toLowerCase(),
                        member_id: store.member_id
                    }).then((response) => {
                        if (response.data.memberExists) {
                            if (!this.errors.has('forum_name_exists')) {
                                this.errors.add({
                                    field: 'forum_name_exists',
                                    msg: 'The desired forum name is already taken'
                                });
                            }
                            store.nameDoesNotExist = false;
                        } else {
                            this.errors.remove('forum_name_exists');
                            store.nameDoesNotExist = true;
                        }
                        store.validating = false;
                    });
                }

            },

            validateMemberId: function () {
                if (store.member_id) {
                    store.validating = true;
                    axios.post(window.Laravel.appPath + '/validate-id/' + store.member_id)
                        .then((response) => {
                            if (!response.data.is_member) {
                                store.validMemberId = false;
                                if (!this.$validator.errors.has('member_id')) {
                                    this.$validator.errors.add({
                                        field: 'member_id',
                                        msg: 'The provided member id is invalid'
                                    });
                                }
                            } else {
                                this.$validator.errors.remove('member_id');
                                store.validMemberId = true;
                            }

                            if (!response.data.verified_email) {
                                if (!this.$validator.errors.has('member_id_email')) {
                                    this.$validator.errors.add({
                                        field: 'member_id_email',
                                        msg: 'The provided member has not completed email verification'
                                    });
                                }
                                store.verifiedEmail = false;
                            } else {
                                this.$validator.errors.remove('member_id_email')
                                store.verifiedEmail = true;
                            }
                            store.validating = false;
                        });
                }
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
                store.rank = 1;
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