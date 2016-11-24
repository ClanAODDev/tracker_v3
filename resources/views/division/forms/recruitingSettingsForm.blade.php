<form id="recruiting-links" method="post"
      action="{{ action('DivisionController@update', $division->abbreviation) }}">

    {{ method_field('PATCH') }}

    <fieldset>
        <legend><i class="fa fa-user-plus"></i> Recruiting Settings <button type="submit" class="btn btn-success pull-right btn-xs">Save changes</button></legend>

        <div class="row">

            <div class="col-md-6 repeater">
                <div class="panel panel-default">

                    <div class="panel-heading">Processing steps</div>

                    <div class="panel-body">Provide any additional steps your recruiters must take in order to process your new recruit into your division.</div>

                    <table data-repeater-list="recruiting_tasks" class="table table-striped table-hover">
                        @include('division.partials.recruitingTasks')
                    </table>

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

                    <table data-repeater-list="recruiting_threads" class="table table-striped table-hover">
                        @include('division.partials.recruitingThreads')
                    </table>

                    <div class="panel-footer text-right">
                        <button data-repeater-create class="btn btn-success btn-block" type="button">
                            <i class="fa fa-plus"></i>Add Link
                        </button>
                    </div>
                </div>
            </div>

            {{ csrf_field() }}

        </div>
    </fieldset>
</form>