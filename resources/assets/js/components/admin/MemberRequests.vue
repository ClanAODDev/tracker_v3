<template>
    <div>
        <h4>PENDING REQUESTS <span class="badge">{{ dataRequests.length }}</span></h4>
        <hr />
        <div class="panel panel-filled" v-if="requests.length > 0">
            <table class="table">
                <thead>
                <tr>
                    <th>Member Name</th>
                    <th>Recruiter</th>
                    <th>Division</th>
                    <th class="text-center">Waiting for</th>
                    <th class="text-center col-xs-2">Approve</th>
                    <th class="text-center col-xs-2">Cancel</th>
                </tr>
                </thead>
                <tbody>

                <member-request :data="request"
                                v-for="(request, index) in requests"
                                @approved="(e) => { return approve(e, request, index)}"
                                @cancelled="cancel(request, index)"
                                :key="request.id"></member-request>
                </tbody>

            </table>
        </div>
        <p v-else>
            <span class="text-success">All done!</span> There are no more requests to approve at this time.
        </p>
        <modal v-show="isModalVisible"
               @close="closeModal">
            <template slot="header">
                <h4>Cancel Member Request</h4>
            </template>
            <template slot="body">
                <label for="notes">Reason for cancellation</label>
                <textarea name="notes" id="notes" rows="3" class="form-control" v-model="notes"
                          required
                          style="resize: vertical" maxlength="100"></textarea>
                <form-error v-if="errors.notes" :errors="errors">
                    @{{ errors.notes }}
                </form-error>
            </template>
            <template slot="footer">
                <button class="btn btn-warning"
                        @click="cancelConfirm">
                    Cancel Request
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
    props: ['requests'],
    data: function () {
      return {
        dataRequests: this.requests,
        isModalVisible: false,
        notes: '',
        requestIndex: null,
        request: null,
      };
    },
    methods: {
      showModal () {
        this.isModalVisible = true;
      },
      closeModal () {
        this.isModalVisible = false;
        this.notes = null;
      },
      approve (event, request, index) {
        console.log(event.path);
        let settings = 'width=900,height=600,scrollbars=yes';
        window.open(event.path, 'Tracker | Approve Member', settings, true);
        axios.post(window.Laravel.appPath + '/admin/member-requests/' + request.id + '/approve')
          .then((response) => {
            this.dataRequests.splice(index, 1);
            toastr.success('Approved!');
          })
          .catch((error) => {
            toastr.error('Something went wrong...');
          });
      },

      cancel (request, index) {
        this.showModal();
        this.requestIndex = index;
        this.request = request;
      },

      cancelConfirm () {
        axios.post(window.Laravel.appPath + '/admin/member-requests/' + this.request.id + '/cancel', {
          'notes': this.notes
        })
          .then((response) => {
            this.dataRequests.splice(this.requestIndex, 1);
            toastr.warning('Request cancelled!');
            this.closeModal();
            this.notes = '';
          })
          .catch(error => {
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