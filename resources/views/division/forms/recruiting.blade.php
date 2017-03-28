<form id="recruiting-links" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#recruiting-settings">

    {{ method_field('PATCH') }}

    <div class="row">

        <div class="col-md-12 repeater">

            <div class="panel">
                <div class="panel-heading">
                    Recruiting Tasks
                </div>

                <table data-repeater-list="recruiting_tasks" class="table">
                    @include('division.partials.recruitingTasks')
                </table>

                <div class="panel-footer">
                    <button data-repeater-create class="btn btn-default btn-block" type="button">
                        <i class="fa fa-plus text-success"></i> New Task
                    </button>
                </div>
            </div>

        </div>

        <div class="col-md-12 repeater">
            <div class="panel">
                <div class="panel-heading">
                    Rules and Regulations
                </div>

                <div data-repeater-list="recruiting_threads">
                    @include('division.partials.recruitingThreads')
                </div>

                <div class="panel-footer">
                    <button data-repeater-create class="btn btn-default btn-block" type="button">
                        <i class="fa fa-plus text-success"></i> New Link
                    </button>
                </div>

                {{ csrf_field() }}

            </div>
        </div>
    </div>

    <div class="text-right">
        <button type="submit" class="btn btn-success">Save changes</button>
    </div>
</form>