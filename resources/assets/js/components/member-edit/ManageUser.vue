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
            <input type="email" class="form-control" v-model="email" id="email"
                   :value="email" name="email" disabled />
        </div>
        <div class="form-group">
            <label for="role">Account Role</label>
            <select name="role" id="role" v-model="currentRole" class="form-control" @change="assignRole">
                <option v-for="(role, id) in roles" :value="id" :selected="currentRole">
                    {{ role }}
                </option>
            </select>
        </div>
    </div>
</template>

<script>
    export default {
        methods: {
            assignRole: function () {
                axios.post (window.Laravel.appPath + '/update-role', {
                    user: this.userId,
                    role: this.currentRole
                }).then (function (response) {
                    toastr.success ("You successfully updated the user's role!", 'Success');
                }).catch (function (error) {
                    if (error.response.status === 403) {
                        toastr.error ('No change was made', 'You are not authorized', {timeOut: 10000});
                        return;
                    }
                    toastr.error (error, 'Something went wrong while updating user role', {timeOut: 10000});
                });
            }
        },
        data() {
            return {
                currentRole: this.role,
                email: this.eMail,
            }
        },
        props: ['e-mail', 'user-id', 'username', 'roles', 'role']
    }
</script>