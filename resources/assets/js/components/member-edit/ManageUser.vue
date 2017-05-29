<template>
    <div>
        <h4>Manage User</h4>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username"
                   :value="this.username" disabled />
        </div>
        <div class="form-group">
            <label for="email">E-Mail</label>
            <input type="email" class="form-control"
                   v-model="email" id="email" :value="email" name="email" />
        </div>
        <div class="form-group">
            <label for="role">Account Role</label>
            <select name="role" id="role" v-model="currentRole" class="form-control">
                <option v-for="(role, id) in roles" :value="id" :selected="currentRole">
                    {{ role }}
                </option>
            </select>
        </div>

        <div class="panel panel-filled m-t-xl">
            <div class="panel-heading">Access role list</div>
            <div class="panel-body">
                <div class="table-responsive">
                    <role-descriptions :role="currentRole"></role-descriptions>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import RoleDescriptions from './partials/role-description.vue';

    export default {
        components: {
            RoleDescriptions
        },
        methods: {
            doUpdatingRole: function () {
                this.updatingRole = !this.updatingRole;
            },
            assignRole: function () {
                this.doUpdatingRole ();
                axios.post (window.Laravel.appPath + '/update-role', {
                    member: this.member_id,
                    position: null
                }).then (function (response) {
                    // do update of user role
                }).catch (function (error) {
                    toastr.error (error, 'Something went wrong while updating user role', {timeOut: 10000})
                });
                this.doUpdatingRole ();
            }
        },
        data() {
            return {
                updatingRole: false,
                currentRole: this.role,
                email: this.eMail,
            }
        },
        props: ['e-mail', 'user-id', 'username', 'roles', 'role']
    }
</script>