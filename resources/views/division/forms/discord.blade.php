<form id="discord-settings" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#discord-settings">

    {{ method_field('PATCH') }}

    <div class="row">
        <div class="col-md-6">
            <p>All notifications will be sent to your division's officer channel, shown below.</p>
            <p><code>#{{ str_slug($division->name) . '-officers' }}</code></p>
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

