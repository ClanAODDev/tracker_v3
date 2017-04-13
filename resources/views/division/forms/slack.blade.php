<form id="slack-settings" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#slack-settings">

    {{ method_field('PATCH') }}

    <div class="row">
        <div class="col-md-6">

            <div class="form-group">
                <label for="slack_channel" class="control-label">Channel</label>
                <input type="text" id="slack_channel" name="slack_channel"
                       placeholder="#channel-name"
                       value="{{ $division->settings()->slack_channel }}" class="form-control" />
                <span class="help-block"><small>Enter the channel you wish to post updates to.</small></span>
            </div>

            <div class="form-group">
                <label for="slack_webhook_url" class="control-label">Webhook URL</label>
                <input type="text" id="slack_webhook_url" name="slack_webhook_url"
                       class="form-control" disabled="disabled" />
                <span class="help-block"><small>Enter the webhook URL you wish to post updates to. Left blank, the Tracker will default to the <a
                                href="http://clanaod.slack.com"
                                target="_blank">AOD Slack</a>.</small></span>
            </div>
        </div>

        <div class="col-md-6">
            <div class="table-responsive">
                @include ('division.partials.slack-notifications')
            </div>
        </div>

    </div>

    {{ csrf_field() }}

    <div class="text-right m-t-md">
        <button type="submit" class="btn btn-success">Save changes</button>
    </div>
</form>

