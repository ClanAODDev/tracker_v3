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

                    <div style="max-height: 500px; overflow-y: scroll; ">
                        @include('admin.partials.divisionStatusList')
                    </div>

                    <div class="panel-footer text-muted"><small>Divisions are never deleted, for historical purposes.</small></div>
                </div>
            </div>
        </div>

        {{ csrf_field() }}

    </fieldset>

</form>

