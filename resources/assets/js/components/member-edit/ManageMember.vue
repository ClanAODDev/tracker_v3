<template>
    <div class="row">
        <div class="col-sm-6">
            <label for="positions"><h4>Position <i class="fa fa-spinner fa-spin" v-if="updatingPosition"></i></h4>
            </label>
            <select v-model="currentPosition" id="positions" name="positions" class="form-control"
                    @change="assignPosition">
                <option v-for="(position, id) in positions" :value="id" :selected="currentPosition">
                    {{ position }}
                </option>
            </select>
            <small class="help-block">
                Positions are used to denote a member's responsibility within the division. Positions do not grant account access.
            </small>
        </div>
        <div class="col-sm-6">
            <position-description :position="currentPosition"></position-description>
        </div>
    </div>
</template>

<script>
    import PositionDescription from './partials/position-description.vue';

    export default {
        components: {
            'position-description': PositionDescription,
        },
        props: ['positions', 'position', 'member-id'],
        mounted() {

        },
        methods: {
            doUpdatingPosition: function () {
                this.updatingPosition = !this.updatingPosition;
            },
            assignPosition: function () {
                this.doUpdatingPosition ();
                axios.post (window.Laravel.appPath + '/update-position', {
                    member: this.memberId,
                    position: this.currentPosition
                }).then (function () {
                    toastr.success("You successfully updated the member's position!", 'Success');
                }).catch (function (error) {
                    if (error.response.status === 403) {
                        toastr.error ('No change was made', 'You are not authorized', {timeOut: 10000});
                        return;
                    }
                    toastr.error (error, 'Something went wrong while updating member position', {timeOut: 10000})
                });
                this.doUpdatingPosition ();
            }
        },
        data() {
            return {
                updatingPosition: false,
                currentPosition: this.position
            }
        }
    }
</script>