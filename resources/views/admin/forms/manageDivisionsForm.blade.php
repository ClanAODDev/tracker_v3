<form id="division-settings" method="post" class="well"
      action="{{ action('AdminController@updateDivisions') }}">

    {{ method_field('PATCH') }}

    <fieldset>
        <legend><i class="fa fa-toggle-on"></i> Manage Divisions
            <button type="submit" class="btn btn-success pull-right btn-xs">Save changes</button>
        </legend>


        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-primary">
                    <div class="panel-heading">Division statuses</div>
                    <div class="panel-body">
                        <p>Divisions that are not set to active will not be included in census data collection, or be updated by the forum data sync. Divisions that <strong>should not</strong> be active include divisions that are:</p>
                        <ul>
                            <li>No longer running</li>
                            <li>Are purely organizational (floater)</li>
                        </ul>
                    </div>

                    @foreach($divisions as $division)
                        <li class="list-group-item" title="Click to visit division page">
                            {{ $division->name }} <i class="fa fa-arrow-circle-right"></i>
                            <div class="material-switch pull-right">
                                <input type='hidden' value='0' name="divisions[{{ $division->abbreviation }}]">
                                <input id="{{ $division->abbreviation }}" name="divisions[{{ $division->abbreviation }}]" type="checkbox" {{ checked($division->active) }} />
                                <label for="{{ $division->abbreviation }}" class="label-success"></label>
                            </div>
                        </li>
                    @endforeach
                </div>
            </div>
        </div>

        {{ csrf_field() }}

    </fieldset>

</form>

