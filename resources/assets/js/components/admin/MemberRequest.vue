<template>
    <tr>
        <td><code>AOD_{{ name }}</code></td>
        <td>{{ this.data.requester.name }}</td>
        <td>{{ division }}</td>
        <td>{{ this.data.created_at }}</td>
        <td>
            <button @click="approve()"
                    class="btn btn-success approve-request btn-sm"><i
                    class="fa fa-check text-success"></i> Approve
            </button>
        </td>
    </tr>
</template>

<script>
  export default {
    props: ['data'],
    methods: {
      approve () {
        this.$emit('approved', this.id);
      }
    },
    data: function () {
      return {
        id: this.data.id,
        name: this.data.member.name,
        division: this.data.division.name,
        path: this.data.approvePath
      }
    },
  };
</script>