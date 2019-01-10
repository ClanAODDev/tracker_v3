<form id="discord-settings" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#discord-settings">

    {{ method_field('PATCH') }}

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="slack_channel" class="control-label">Channel</label>
                <input type="text" id="slack_channel" name="slack_channel"
                       placeholder="division-name-officers"
                       value="{{ $division->settings()->slack_channel }}" class="form-control" />
                <span class="help-block"><small>Enter the channel you wish to post updates to. Notifications should be sent to your officer channel.</small></span>
            </div>
        </div>

        <div class="col-md-6">
            <label for="discord-notifications-table">Officer Notifications</label>
            <div class="table-responsive" id="discord-notifications-table">
                @include ('division.partials.discord-notifications')
            </div>
        </div>

    </div>

    {{ csrf_field() }}

    <div class="text-right m-t-md">
        <button type="submit" class="btn btn-success">Save changes</button>
    </div>
</form>

