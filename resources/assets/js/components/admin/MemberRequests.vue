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
    </div>
</template>

<script>
  import axios from 'axios';
  import request from './_request';

  export default {
    name: 'ManageRequests',
    components: {
      'member-request': request
    },
    props: ['requests'],
    data: function () {
      return {
        dataRequests: this.requests
      };
    },
    methods: {
      approve (event, request, index) {
        console.log(event.path);
        let settings = 'width=900,height=600,scrollbars=yes';
        window.open(event.path, 'Tracker | Approve Member', settings, true);
        axios.post(window.Laravel.appPath + '/admin/member-requests/' + request.id + '/approve')
          .then((response) => {
            this.dataRequests.splice(index, 1);
            toastr.success('Approved!');
          })
          .catch(error => {
            toastr.error(
              error.response.status + ' - ' + error.response.statusText
            );
          });
      },

      cancel (request, index) {
        axios.post(window.Laravel.appPath + '/admin/member-requests/' + request.id + '/cancel')
          .then((response) => {
            this.dataRequests.splice(index, 1);
            toastr.success('Denied!');
          })
          .catch(error => {
            toastr.error(
              error.response.status + ' - ' + error.response.statusText
            );
          });
      }
    }
  };
</script>