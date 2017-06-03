<template>
    <div>
        <table class="table table-striped">
            <thead>
            <tr>
                <th>Handle Type</th>
                <th>Value</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>

            <tr v-for="handle in myHandles">
                <td class="col-md-3">
                    {{ handle.name }}
                </td>

                <td class="col-md-6">
                    <input type="text" class="form-control" :value="handle.value" />
                </td>

                <td class="col-md-3">
                    <!-- remove action -->
                    <button class="btn btn-danger btn-block">
                        <i class="fa fa-trash text-danger"></i>
                    </button>
                </td>
            </tr>

            <tr>
                <td class="col-md-3 c-white">Add new alias</td>
                <td class="col-md-6">
                    <select id="alias-selector" class="form-control">
                        <option value="" disabled="" selected="selected">Select an alias type</option>
                        <option :value="id" v-for="(id, name) in allHandles">{{ name }}</option>
                    </select>
                </td>
                <td class="col-md-3">
                    <button class="btn btn-success btn-block add-alias">
                        <i class="fa fa-plus fa-lg"></i></button>
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
                handles: []
            }
        },

        mounted() {
            this.getMyHandles ();
        },

        methods: {
            /**
             * accepts prop value and populates handles
             */
            getMyHandles: function () {
                this.handles = this.myHandles;
            }
        },

        props: ['allHandles', 'myHandles', 'member-id', 'division-primary']
    }
</script>