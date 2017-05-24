<template>
    <div class="panel panel-filled">
        <div class="panel-heading">
            <strong class="text-uppercase">Information</strong>
        </div>
        <div class="panel-body">

            <p>Please be careful and ensure member information is entered accurately. Forum names can be changed when the member status request is submitted, and changed names will sync with the tracker automatically.</p>

            <div class="row">

                <div class="col-md-4 form-group" :class="{'input': true, 'has-error': errors.has('member_id') }">
                    <label for="member_id">Forum Member Id</label>
                    <input type="number" class="form-control" name="member_id" v-model="Store.member_id"
                           id="member_id" v-validate="'max:5'" required />
                    <span v-show="errors.has('member_id')" class="help-block">{{ errors.first('member_id') }}</span>
                </div>

                <div class="col-md-4 form-group">
                    <label for="forum_name">Forum Name</label>
                    <input type="text" class="form-control" name="forum_name" v-model="Store.forum_name"
                           @blur="forumNameToIngameName" id="forum_name" required />
                </div>

                <div class="col-md-4 form-group">
                    <label for="ingame_name">Ingame Name</label>
                    <input type="text" class="form-control" name="ingame_name"
                           v-model="Store.ingame_name" id="ingame_name" required />
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    import Store from '../store.js';

    export default {
        data() {
            return {
                Store
            }
        },

        methods: {
            forumNameToIngameName: function () {
                Store.ingame_name = Store.forum_name;
            }
        }
    };
</script>