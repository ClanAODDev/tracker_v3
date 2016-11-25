<form id="locality-settings" method="post" class="well"
      action="{{ action('DivisionController@update', $division->abbreviation) }}">
    <fieldset>
        <legend><i class="fa fa-language"></i> Locality
            <button type="submit" class="btn btn-success pull-right btn-xs">Save changes</button>
        </legend>

        {{ method_field('PATCH') }}

        <div class="panel panel-default">
            <div class="panel-heading">String Conversions</div>

            <div class="panel-body">
                <p>This section allows you to customize the tracker's language to match your game's specific terminology. Entries should be all lower-case and singular.</p>
                <p>Use the
                    <code>Old String</code> column as a guide to determine what each term should display as. Terms you wish to remain unchanged should match on both columns.
                </p>
            </div>

            <table class="table table-striped table-hover">
                @include('division.partials.locality')
            </table>

            <div class="panel-footer text-right">
                <button type="button" class="btn btn-primary" data-reset-locality>
                    <i class="fa fa-undo"></i>Reset to default
                </button>
            </div>
        </div>

        {{ csrf_field() }}

    </fieldset>
</form>