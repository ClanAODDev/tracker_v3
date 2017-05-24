<template>
    <div class="m-t-xl">
        <div class="panel panel-filled" v-if="Store.division.platoons">
            <div class="panel-heading text-uppercase">
                <strong>Assignment</strong>
            </div>
            <div class="panel-body">
                <p>Depending on your division's configuration, a {{ Store.locality.platoon }} and {{ Store.locality.squad }} assignment may be required.</p>

                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label for="platoon">{{ Store.locality.platoons }}</label>
                        <select name="platoon" id="platoon" class="form-control" v-model="Store.platoon"
                                @change="Store.getPlatoonSquads(Store.platoon)">
                            <option value="">Select a platoon...</option>
                            <option :value="id" v-for="(name, id, index) in Store.division.platoons">
                                {{ name }}
                            </option>
                        </select>
                    </div>
                    <div class="col-sm-6 form-group">
                        <label for="squad">{{ Store.locality.squads }}</label>
                        <select name="squad" id="squad" class="form-control" :disabled="! Store.division.squads.length">
                            <option v-if="! Store.division.squads.length">No squads available</option>
                            <option :value="squad.id" v-for="squad in Store.division.squads">
                                {{ (squad.name) ? squad.name : 'Squad #' + squad.id }} - {{ (squad.leader) ? squad.leader.name : 'TBA' }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-filled panel-c-danger" v-else>
            <div class="panel-heading text-uppercase">
                <strong>Assignment</strong>
            </div>
            <div class="panel-body">
                <p>
                    <i class="fa fa-exclamation-triangle text-danger"></i> Your division has no {{ Store.locality.platoons }}, so assignment is not unavailable. A division leader will need to create one.
                </p>
            </div>
        </div>
    </div>

</template>

<script>
    import Store from '../store.js';

    export default {

        mounted() {
        },

        methods: {},

        data() {
            return {
                Store
            }
        }
    }
</script>