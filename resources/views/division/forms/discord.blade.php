<form id="discord-settings" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#discord-settings">

    {{ method_field('PATCH') }}

    <div class="row">
        <div class="col-md-6">
            <label for="discord-notifications-table">Officer Notifications</label>
            <div class="table-responsive" id="discord-notifications-table">
                @include ('division.partials.discord-notifications')
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="officer_channel" class="control-label">Channel</label>
                <input type="text"
                       readonly disabled
                       placeholder="discord-id-should-be-here"
                       value="{{ $division->settings()->officer_channel }}" class="form-control disabled" />
                <span class="help-block"><small>Discord channel id where officer notifications will be
                        sent. Value set by AOD Discord Bot.</small></span>
            </div>
        </div>

    </div>

    {{ csrf_field() }}

    <div class="text-right m-t-md">
        <button type="submit" class="btn btn-success">Save changes</button>
    </div>
</form>

