<template>
    <div>
        <div class="btn-group">
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

        <div class="panel panel-filled m-t-lg">
            <div class="panel-body">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th class="text-center">Status</th>
                        <th>Handle Type</th>
                        <th>Handle Value</th>
                    </tr>
                    </thead>
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
                                   :placeholder="handle.enabled ? 'Enter handle value' : 'Activate to enter a value'"
                                   :required="handle.enabled" @keydown="changesMade = true"

                            />
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</template>

<script>
  export default {
    data() {
      return {
        myHandles: [],
        changesMade: false,
        showDisabled: false,
      };
    },

    mounted() {
      this.myHandles = this.handles;

      let divisionHandle = this.divisionHandle,
        hasDivisionHandle = this.myHandles.filter(function (handle) {
            return divisionHandle === handle.type;
          }).length > 0;

      console.log(hasDivisionHandle);

    },

    methods: {
      toggleHandle: function (handle) {
        this.changesMade = true;
        handle.enabled = !handle.enabled;
      },
      storeHandles: function () {
        let handles = this.myHandles.filter(function (handle) {
          return handle.enabled && handle.value;
        });

        axios.post(window.Laravel.appPath + '/update-handles', {
          member_id: this.memberId,
          handles: handles
        }).then(function (response) {
          window.location.reload();
        }).catch(function (error) {
          toastr.error(error, 'Something went wrong');
        });

        this.changesMade = false;
      }
    },

    props: ['handles', 'member-id', 'division-handle']
  };
</script>