<form id="recruiting-links" method="post"
      action="{{ route('updateDivision', $division->slug) }}#recruiting-settings">

    {{ method_field('PATCH') }}

    <div class="row">

        <div class="col-md-12 repeater">
            <div class="panel collapsed panel-filled panel-collapse">
                <div class="panel-heading panel-toggle">
                    <div class="panel-tools">
                        <i class="fa toggle-icon fa-chevron-down"></i>
                    </div>
                    <i class="fa fa-check-circle-o text-accent"></i> Recruiting Tasks
                </div>

                <div class="panel-body">
                    <table data-repeater-list="recruiting_tasks" class="table" id="sortable">
                        <tbody>
                        @include('division.partials.recruitingTasks')
                        </tbody>
                    </table>
                </div>

                <div class="panel-footer">
                    <button data-repeater-create class="btn btn-default btn-block" type="button">
                        <i class="fa fa-plus text-success"></i> New Task
                    </button>
                </div>
            </div>

        </div>

        <div class="col-md-12 repeater">
            <div class="panel collapsed panel-filled panel-collapse">
                <div class="panel-heading panel-toggle">
                    <div class="panel-tools">
                        <i class="fa toggle-icon fa-chevron-down"></i>
                    </div>
                    <i class="fa fa-pencil-square-o text-accent"></i> Rules and Regulations
                </div>
                <div class="panel-body">
                    <div data-repeater-list="recruiting_threads">
                        @include('division.partials.recruitingThreads')
                    </div>
                </div>
                <div class="panel-footer">
                    <button data-repeater-create class="btn btn-default btn-block" type="button">
                        <i class="fa fa-plus text-success"></i> New Link
                    </button>
                </div>
            </div>

            <div class="panel panel-filled collapsed panel-collapse">
                <div class="panel-heading panel-toggle">
                    <div class="panel-tools">
                        <i class="fa toggle-icon fa-chevron-down"></i>
                    </div>
                    <i class="fa fa-envelope-o text-accent"></i> Welcome PM
                </div>
                <div class="panel-body">
                    <textarea class="form-control resize-vertical" name="welcome_pm" id="welcome_pm" cols="30"
                              rows="10">{{ $division->settings() ->get('welcome_pm')}}</textarea>
                    <span class="help-block m-t-md" for="welcome_pm">Use <code>@{{ name }}</code> to insert the new recruit's name into your message</span>
                </div>
            </div>
        </div>
    </div>

    <div class="text-right">
        <button type="submit" class="btn btn-success">Save changes</button>
    </div>

    {{ csrf_field() }}
</form>

<script>
  $(function () {
    $('#sortable tbody').sortable();
  });
</script>