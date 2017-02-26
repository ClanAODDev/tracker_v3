<form id="recruiting-links" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#recruiting-settings">

    {{ method_field('PATCH') }}

    <div class="row">

        <div class="col-md-6 repeater">
            <div class="panel panel-filled">

                <div class="panel-body">Provide any additional steps your recruiters must take in order to process your new recruit into your division.</div>

                <table data-repeater-list="recruiting_tasks" class="table">
                    @include('division.partials.recruitingTasks')
                </table>

                <div class="panel-footer text-right">
                    <button data-repeater-create class="btn btn-default btn-block" type="button">
                        <i class="fa fa-plus"></i> New Task
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6 repeater">
            <div class="panel-body">
                If there are additional threads a new recruit must respond to before being accepted to your division, you can provide those below.
            </div>

            <div class="panel panel-filled">
                <table data-repeater-list="recruiting_threads" class="table">
                    @include('division.partials.recruitingThreads')
                </table>

                <div class="panel-footer text-right">
                    <button data-repeater-create class="btn btn-default btn-block" type="button">
                        <i class="fa fa-plus"></i> New Link
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