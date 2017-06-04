<template>
    <div>

        <table class="table table-condensed">
            <thead>
            <tr>
                <th>Handle Type</th>
                <th>Handle Value</th>
                <th class="text-center">Active</th>
            </tr>
            </thead>
            <tbody>

            <tr v-for="handle in myHandles">
                <td class="col-md-3" :class="!handle.enabled ? 'text-muted' : ''">
                    {{ handle.name }}
                </td>
                <td class="col-md-8">
                    <input type="text" class="form-control" :value="handle.value"
                           :disabled="!handle.enabled" v-model="handle.value"
                           :placeholder="handle.comments ? handle.comments : 'Enter value'"
                           :required="handle.enabled" @keydown="changesMade = true"
                    />
                </td>

                <td class="col-md-1">
                    <button class="btn btn-success btn-block" v-if="handle.enabled"
                            @click="toggleHandle(handle)">
                        <i class="fa fa-check text-success"></i>
                    </button>
                    <button class="btn btn-danger btn-block" type="button"
                            @click="handle.enabled = !handle.enabled" v-else>
                        <i class="fa fa-times text-danger"></i>
                    </button>

                </td>
            </tr>
            </tbody>
        </table>
        <button class="btn btn-default" type="submit" @click="storeHandles"
                :disabled="!changesMade" :class="changesMade ? 'btn-success' : ''">
            <i class="fa fa-save"></i>
            Save Handles
        </button>

    </div>
</template>

<script>
    export default {
        data() {
            return {
                myHandles: [],
                changesMade: false,
            }
        },

        mounted() {
            this.myHandles = this.handles;
        },

        methods: {
            toggleHandle: function (handle) {
                this.changesMade = true;
                handle.enabled = ! handle.enabled;
            },
            storeHandles: function () {
                let handles = this.myHandles.filter (function (handle) {
                    return handle.enabled && handle.value;
                });

                axios.post (window.Laravel.appPath + '/update-handles', {
                    member_id: this.memberId,
                    handles: handles
                }).then (function (response) {
                    toastr.success ('Member handles have been updated!');
                }).catch (function (error) {
                    toastr.error (error, 'Something went wrong')
                });

                this.changesMade = false;
            }
        },

        props: ['handles', 'member-id']
    }
</script>