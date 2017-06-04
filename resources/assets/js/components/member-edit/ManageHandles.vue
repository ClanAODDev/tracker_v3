<template>
    <div>

        <h4>Manage Ingame Handles</h4>

        <p>Here you can manage all of a member's ingame handles. All divisions have a default primary ingame handle, so if this member belongs to you, ensure that it exists and is accurate.</p>

        <p>To add a handle, first <code>show all</code> and activate the handle(s) you wish to add. Then provide values, and finally, save your changes.</p>

        <div class="btn-group m-t-lg">
            <button class="btn-default btn" @click="showDisabled = !showDisabled">
                <i class="fa fa-eye" v-if="showDisabled"></i>
                <i class="fa fa-eye-slash" v-else="showDisabled"></i>
                Show all
            </button>
            <button class="btn btn-default pull-right" type="submit" @click="storeHandles"
                    :disabled="!changesMade" :class="changesMade ? 'btn-success' : ''">
                <i class="fa fa-save"></i>
                Save Handles
            </button>
        </div>

        <table class="table m-t-sm table-bordered">
            <tbody>

            <!-- hide handles unless they are enabled or showDisabled is true -->
            <tr v-for="handle in myHandles" v-show="! handle.enabled ? showDisabled : true">
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
                <td class="col-md-3" :class="!handle.enabled ? 'text-muted' : ''">
                    {{ handle.label }}
                </td>
                <td class="col-md-8">
                    <input type="text" class="form-control" :value="handle.value"
                           :disabled="!handle.enabled" v-model="handle.value"
                           :placeholder="handle.comments ? handle.comments : 'Enter value'"
                           :required="handle.enabled" @keydown="changesMade = true"
                    />
                </td>
            </tr>
            </tbody>
        </table>
    </div>

</template>

<script>
    export default {
        data() {
            return {
                myHandles: [],
                changesMade: false,
                showDisabled: false,
            }
        },

        mounted() {
            this.myHandles = this.handles;

            let divisionHandle = this.divisionHandle,
                hasDivisionHandle = this.myHandles.filter (function (handle) {
                        return divisionHandle === handle.type
                    }).length > 0;

            console.log (hasDivisionHandle);

        },

        methods: {
            toggleHandle: function (handle) {
                this.changesMade = true;
                handle.enabled = !handle.enabled;
            },
            storeHandles: function () {
                let handles = this.myHandles.filter (function (handle) {
                    return handle.enabled && handle.value;
                });

                axios.post (window.Laravel.appPath + '/update-handles', {
                    member_id: this.memberId,
                    handles: handles
                }).then (function (response) {
                    window.location.reload ();
                }).catch (function (error) {
                    toastr.error (error, 'Something went wrong')
                });

                this.changesMade = false;
            }
        },

        props: ['handles', 'member-id', 'division-handle']
    }
</script>