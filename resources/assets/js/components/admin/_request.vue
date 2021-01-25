<template>
  <tr :class="{'grace-period': !isPastGracePeriod}">
    <td>
      <i class="fa fa-hourglass-half" v-show="!isPastGracePeriod" title="Grace period"></i>
      <div class="input-group" v-if="editing">
        <input type="text" class="form-control" v-model="name"
               @input="buildPath">
        <span class="input-group-btn m-l-sm">
                    <button class="btn btn-default" type="button"
                            @click="editing = false">
                        <i class="fa fa-check text-success"></i>
                    </button>
                </span>
      </div>
      <code v-else @click="editing = true" style="cursor:pointer;">{{ name }}</code>
    </td>
    <td>{{ this.data.requester.name }}</td>
    <td>{{ division }}</td>
    <td class="text-center">{{ this.data.timeWaiting }}</td>
    <td>

      <div class="col-md-6 col-sm-12">
        <button @click="approve()"
                class="btn btn-success btn-block">
          <small class="text-uppercase"><i class="fa fa-user-plus"></i> Approve</small>
        </button>
      </div>
      <div class="col-md-6 col-sm-12">
        <button @click="placeOnHold()"
                class="btn btn-warning btn-block">
          <small class="text-uppercase"><i class="fa fa-hourglass-half"></i> Hold</small>
        </button>
      </div>
    </td>
  </tr>
</template>

<script>
export default {
  props: ['data'],
  created() {
    this.buildPath();
  },

  computed: {
    isPastGracePeriod() {
      console.log(this.data.isPastGracePeriod);
      return (this.data.isPastGracePeriod);
    },
  },
  methods: {

    approve() {
      this.$emit('approved', {id: this.id, path: this.path});

      if (this.name !== this.data.name) {
        this.$emit('name-changed', {oldName: this.data.name, newName: this.name, id: this.id});
      }
    },
    placeOnHold() {
      this.$emit('placedOnHold', this.id);
    },
    buildPath() {
      this.path = this.data.approvePath + this.name;
    }
  },
  data: function () {
    return {
      editing: false,
      id: this.data.id,
      name: this.data.name,
      division: this.data.division.name,
      path: '',
      notes: '',
    };
  },
};
</script>

<style>
.grace-period {
  opacity: .35;
}
</style>