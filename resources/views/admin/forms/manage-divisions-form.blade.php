<div class="row">
    <div class="col-md-6">
        <div class="panel">
            <h3>Division Statuses</h3>
            <p>Manage division active status across the tracker. Divisions that are not set to active will not be included in census data collection, be updated by the forum data sync, or be listed in the tracker interface. Users will also not be able to view a division on the tracker that is set to inactive.</p>

            <p>Divisions that
                <strong>should not</strong> be active include divisions that are:</p>
            <ul>
                <li>No longer running</li>
                <li>Archived for historical purposes</li>
                <li>Are purely organizational (floater)</li>
            </ul>
        </div>
    </div>
    <div class="col-md-6">
        <form id="division-settings" method="post" action="{{ route('updateDivisions') }}#divisions">
            <div class="panel panel-filled">

                {{ method_field('PATCH') }}

                @include('admin.partials.division-status-list')

                {{ csrf_field() }}

                <div class="panel-footer text-muted">
                    <button type="submit" class="btn btn-success pull-right">Update statuses</button>
                    <div class="clearfix"></div>
                </div>
            </div>
        </form>
    </div>
</div>



