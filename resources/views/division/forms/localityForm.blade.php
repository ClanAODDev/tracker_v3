<form id="locality-settings" method="post" class="well"
      action="{{ action('DivisionController@update', $division->abbreviation) }}">
    <fieldset>
        <legend><i class="fa fa-language"></i> Locality
            <button type="submit" class="btn btn-success pull-right btn-xs">Save changes</button>
        </legend>

        {{ method_field('PATCH') }}

        <div class="panel panel-default">
            <div class="panel-heading">String Conversions</div>

            <div class="panel-body">This section allows you to customize the tracker's language to match your game's specific terminology. Entries should be all lower-case.</div>

            <table class="table table-striped table-hover">
                @include('division.partials.locality')
            </table>
        </div>

        {{ csrf_field() }}

    </fieldset>
</form>