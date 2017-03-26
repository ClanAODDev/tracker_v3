<form id="slack-settings" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#slack-settings">

    {{ method_field('PATCH') }}

    <div class="row">
        <div class="col-md-5">

            <div class="form-group">
                <label for="slack_webhook_url" class="control-label">Webhook URL</label>
                <input type="text" id="slack_webhook_url" name="slack_webhook_url"
                       placeholder="https://hooks.slack.com/services/..."
                       value="{{ $division->settings()->slack_channel }}" class="form-control" />
                <span class="help-block"><small>Enter the webhook URL you wish to post updates to. Left blank, the Tracker will default to the <a
                                href="http://clanaod.slack.com"
                                target="_blank">AOD Slack</a>.</small></span>
            </div>
            <div class="form-group">
                <label for="slack_channel" class="control-label">Channel</label>
                <input type="text" id="slack_channel" name="slack_channel"
                       placeholder="#channel-name"
                       value="{{ $division->settings()->slack_channel }}" class="form-control" />
                <span class="help-block"><small>Enter the channel you wish to post updates to.</small></span>
            </div>
        </div>

        <div class="col-md-6 pull-right">

            <div class="table-responsive">
                <div class="list-group-item text-muted text-uppercase">
                    Member
                </div>

                <div class="list-group-item">

                    <label for="slack_alert_created_member">
                        When a new member is recruited
                    </label>
                    <div class="pull-right">
                        <input type='hidden' value='0' name="slack_alert_created_member">
                        <input id="slack_alert_created_member"
                               name="slack_alert_created_member"
                               type="checkbox" {{ checked($division->settings()->slack_alert_created_member) }} />
                    </div>

                </div>

                <div class="list-group-item">

                    <label for="slack_alert_removed_member">
                        When a member is removed
                        <i class="fa fa-exclamation-triangle text-danger"
                           aria-hidden="true" title="Potentially spammy"></i>
                    </label>
                    <div class="pull-right">
                        <input type='hidden' value='0' name="slack_alert_removed_member">
                        <input id="slack_alert_removed_member"
                               name="slack_alert_removed_member"
                               type="checkbox" {{ checked($division->settings()->slack_alert_removed_member) }} />
                    </div>

                </div>

                <div class="list-group-item">

                    <label for="slack_alert_updated_member">
                        When a member's profile is edited
                        <i class="fa fa-exclamation-triangle text-danger"
                           aria-hidden="true" title="Potentially spammy"></i>
                    </label>
                    <div class=" pull-right">
                        <input type='hidden' value='0' name="slack_alert_updated_member">
                        <input id="slack_alert_updated_member"
                               name="slack_alert_updated_member"
                               type="checkbox" {{ checked($division->settings()->slack_alert_updated_member) }}/>
                    </div>

                </div>

                <div class="list-group-item text-muted text-uppercase">
                    Division
                </div>

                <div class="list-group-item">

                    <label for="slack_alert_created_request">
                        When a request is submitted
                    </label>
                    <div class="pull-right">
                        <input type='hidden' value='0' name="slack_alert_created_request">
                        <input id="slack_alert_created_request"
                               name="slack_alert_created_request"
                               type="checkbox" {{ checked($division->settings()->slack_alert_created_request) }} />
                    </div>

                </div>
            </div>
        </div>

    </div>

    {{ csrf_field() }}

    <button type="submit" class="btn btn-success pull-right">Save changes</button>
</form>

