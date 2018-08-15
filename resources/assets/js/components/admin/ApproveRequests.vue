<template>
    <div>
        <h4>PENDING REQUESTS <span class="badge">{{ dataRequests.length }}</span></h4>
        <hr />

        <div class="row">
            <div class="col-xs-12">
                <table class="table table-striped"
                       v-if="requests.length > 0">
                    <thead>
                    <tr>
                        <th>Member Name</th>
                        <th>Recruiter</th>
                        <th>Division</th>
                        <th>Requested</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <member-request :data="request"
                                    v-for="(request, index) in requests"
                                    @approved="approve(request, index)"
                                    :key="request.id"></member-request>
                    </tbody>
                </table>
                <p v-else>
                    <span class="text-success">All done!</span> There are no more requests to approve at this time.
                </p>
            </div>
        </div>
    </div>
</template>

<script>
  import axios from 'axios';
  import request from './MemberRequest';

  export default {
    name: 'ApproveRequests',
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
      approve (request, index) {
        let settings = 'width=900,height=600,scrollbars=yes';
        // window.open(request.path, 'Tracker | Approve Member', settings, true);
        axios.post(window.Laravel.appPath + '/admin/member-requests/approve/' + request.id)
          .then((response) => {
            this.dataRequests.splice(index, 1);
            toastr.success('Approved!');
          })
          .catch(error => {
            toastr.error(error.response.data);
          });
      }
    }
  };
</script>