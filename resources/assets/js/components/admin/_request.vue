<template>
    <tr>
        <td>
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
            <button @click="approve()"
                    class="btn btn-success btn-block">
                <i class="fa fa-check text-success"></i>
            </button>
        </td>
        <td>
            <button @click="cancel()"
                    class="btn btn-danger btn-block">
                <i class="fa fa-trash text-danger"></i>
            </button>
        </td>
    </tr>
</template>

<script>
  export default {
    props: ['data'],
    created () {
      this.buildPath();
    },
    methods: {
      approve () {
        this.$emit('approved', {id: this.id, path: this.path});
      },
      cancel () {
        this.$emit('cancelled', this.id);
      },
      buildPath () {
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