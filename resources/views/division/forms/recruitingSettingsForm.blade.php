<div class="well">
    <fieldset>
        <legend><i class="fa fa-user-plus"></i> Recruiting Settings</legend>

        <div class="row">

            <form id="recruiting-links" method="post"
                  action="{{ action('DivisionController@update', $division->abbreviation) }}">

                {{ method_field('PATCH') }}

                <div class="col-md-6 repeater">
                    <div class="panel panel-default">

                        <div class="panel-heading">Processing steps</div>

                        <div class="panel-body">Provide any additional steps your recruiters must take in order to process your new recruit into your division.</div>

                        <div data-repeater-list="tasks">

                            <div class="list-group-item" data-repeater-item>
                                <div class="row">

                                    <div class="col-md-10">
                                        <input type="text" name="tasks[0][task-description]"
                                               class="form-control" placeholder="Add a task"/>
                                    </div>

                                    <div class="col-md-2">
                                        <button type="button" data-repeater-delete class="btn btn-danger">
                                            <i class="fa fa-trash-o fa-lg"></i></button>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <div class="panel-footer text-right">
                            <button data-repeater-create class="btn btn-success btn-block" type="button">
                                <i class="fa fa-plus"></i>Add Task
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 repeater">

                    <div class="panel panel-default">

                        <div class="panel-heading">Threads required for your division</div>

                        <div class="panel-body">If there are additional threads a new recruit must respond to before being accepted to your division, you can provide those below.</div>

                        <div data-repeater-list="threads">

                            <div class="list-group-item" data-repeater-item>
                                <div class="row">

                                    <div class="col-md-6">
                                        <input type="text" name="threads[0][thread-name]"
                                               class="form-control" placeholder="Thread Name"/>
                                    </div>

                                    <div class="col-md-4">
                                        <input type="number" name="threads[0][thread-id]"
                                               class="form-control" placeholder="Thread ID"/>
                                    </div>

                                    <div class="col-md-2">
                                        <button type="button" data-repeater-delete class="btn btn-danger">
                                            <i class="fa fa-trash-o fa-lg"></i></button>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <div class="panel-footer text-right">
                            <button data-repeater-create class="btn btn-success btn-block" type="button">
                                <i class="fa fa-plus"></i>Add Link
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-default btn-lg pull-right">Save changes</button>
                    </div>
                </div>

                {{ csrf_field() }}
            </form>
        </div>
    </fieldset>
</div>

<script>
    $(document).ready(function () {
        $('.repeater').repeater({
            isFirstItemUndeletable: true
        });
    });
</script>