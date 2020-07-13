<template>
    <div>
        <h4>PENDING REQUESTS <span class="badge">{{ dataPending.length }}</span></h4>
        <hr/>
        <div class="panel panel-filled" v-if="dataPending.length > 0">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>Member Name</th>
                    <th>Recruiter</th>
                    <th>Division</th>
                    <th class="text-center">Waiting for</th>
                    <th class="text-center col-xs-2">Actions</th>
                </tr>
                </thead>
                <tbody>

                <member-request :data="request"
                                v-for="(request, index) in pending"
                                @approved="(e) => { return approve(e, request, index)}"
                                @cancelled="cancel(request, index)"
                                @placedOnHold="placeOnHold(request, index)"
                                @name-changed="(e) => {return notifyOfNameChange(e, oldName, newName) }"
                                :key="request.id"></member-request>
                </tbody>

            </table>
        </div>
        <p v-else>
            <span class="text-success">All done!</span> There are no more requests to approve at this time.
        </p>

        <modal v-show="isCancelModalVisible"
               @close="closeCancelModal">
            <template slot="header">
                <h4>Cancel Member Request</h4>
            </template>
            <template slot="body">
                <label for="notes">Reason for cancellation</label>
                <textarea name="notes" id="notes" rows="3" class="form-control" v-model="notes"
                          required
                          style="resize: vertical" maxlength="100"></textarea>
            </template>
            <template slot="footer">
                <button class="btn btn-warning"
                        @click="cancelConfirm">
                    Cancel Request
                </button>
            </template>
        </modal>

        <modal v-show="isHoldModalVisible"
               @close="closeHoldModal">
            <template slot="header">
                <h4>Place Member Request On Hold</h4>
            </template>
            <template slot="body">
                <label for="notes">Placed on hold for:</label>
                <textarea name="notes" id="notes" rows="3" class="form-control" v-model="notes"
                          required
                          style="resize: vertical" maxlength="100"></textarea>
            </template>
            <template slot="footer">
                <button class="btn btn-warning"
                        @click="placeOnHoldConfirm">
                    Place on hold
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
    import axios from 'axios';
    import request from './_request';
    import modal from '../partials/modal';

    export default {
        name: 'ManageRequests',
        components: {
            'member-request': request,
            modal
        },
        props: ['pending'],
        data: function () {
            return {
                dataPending: this.pending,
                isCancelModalVisible: false,
                isHoldModalVisible: false,
                notes: '',
                requestIndex: null,
                request: null,
                oldName: null,
                newName: null,
            };
        },
        methods: {

            showCancelModal() {
                this.isCancelModalVisible = true;
            },

            closeCancelModal() {
                this.isCancelModalVisible = false;
                this.notes = null;
            },

            showHoldModal() {
                this.isHoldModalVisible = true;
            },

            closeHoldModal() {
                this.isHoldModalVisible = false;
                this.notes = null;
            },

            notifyOfNameChange(event) {
                axios.post(window.Laravel.appPath + '/clan/member-requests/' + event.id + '/name-change', {
                    oldName: event.oldName,
                    newName: event.newName,
                });
            },

            approve(event, request, index) {

                /**
                 * Check if member was already approved
                 */
                axios.get(window.Laravel.appPath + '/clan/member-requests/' + request.id + '/validate').then((response) => {

                    if (!response.data.isMember) {

                        /**
                         * Ensure popup was created
                         */
                        let popup = window.open(event.path, '_blank', null);
                        if (!popup || popup.closed || typeof popup.closed == 'undefined') {
                            toastr.error('Something prevented the modcp popup from opening.', 'Oops!');
                            return false;
                        }

                        axios.post(window.Laravel.appPath + '/clan/member-requests/' + request.id + '/approve')
                            .then((response) => {
                                this.dataPending.splice(index, 1);
                                toastr.success('Approved!');
                            }).catch((error) => {
                            toastr.error('Something went wrong...');
                        });
                    } else {
                        toastr.warning('Member already approved. Cleaning up...');
                        this.dataPending.splice(index, 1);
                    }
                });
            },

            cancel(request, index) {
                this.showCancelModal();
                this.requestIndex = index;
                this.request = request;
            },

            placeOnHold(request, index) {
                this.showHoldModal();
                this.requestIndex = index;
                this.request = request;
            },

            cancelConfirm() {
                axios.post(window.Laravel.appPath + '/clan/member-requests/' + this.request.id + '/cancel', {
                    'notes': this.notes
                }).then(response => {
                    this.dataPending.splice(this.requestIndex, 1);
                    toastr.warning('Request cancelled!');
                    this.closeModal();
                    this.notes = '';
                }).catch(error => {
                    if (error.response.data.errors.notes) {
                        toastr.error(error.response.data.errors.notes);
                    } else {
                        toastr.error('Something went wrong');
                    }
                });
            },

            placeOnHoldConfirm() {
                axios.post(window.Laravel.appPath + '/clan/member-requests/' + this.request.id + '/hold', {
                    'notes': this.notes
                }).then(response => {
                    this.dataPending.splice(this.requestIndex, 1);
                    toastr.warning('Request put on hold!');
                    this.closeHoldModal();
                    this.notes = '';
                }).catch(error => {
                    if (error.response.data.errors.notes) {
                        toastr.error(error.response.data.errors.notes);
                    } else {
                        toastr.error('Something went wrong');
                    }
                });
            }
        }
    };
</script>