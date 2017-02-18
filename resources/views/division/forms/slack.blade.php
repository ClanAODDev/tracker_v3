<form id="slack-settings" method="post"
      action="{{ route('updateDivision', $division->abbreviation) }}#slack-settings">

    {{ method_field('PATCH') }}

    <div class="row">
        <div class="col-md-6">

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

        <div class="col-md-6">
            <div class="form-group">

                <h5>Notify when the following events occur...</h5>

                <li class="list-group-item text-muted">MEMBER</li>

                <li class="list-group-item">
                    When a new member is recruited
                    <div class="material-switch pull-right">
                        <input type='hidden' value='0' name="slack_alert_created_member">
                        <input id="slack_alert_created_member"
                               name="slack_alert_created_member"
                               type="checkbox" {{ checked($division->settings()->slack_alert_created_member) }} />
                        <label for="slack_alert_created_member" class="label-success"></label>
                    </div>
                </li>

                <li class="list-group-item">
                    When a member is removed <i class="fa fa-exclamation-triangle text-danger"
                                                aria-hidden="true"
                                                title="Potentially spammy"></i>
                    <div class="material-switch pull-right">
                        <input type='hidden' value='0' name="slack_alert_removed_member">
                        <input id="slack_alert_removed_member"
                               name="slack_alert_removed_member"
                               type="checkbox" {{ checked($division->settings()->slack_alert_removed_member) }} />
                        <label for="slack_alert_removed_member" class="label-success"></label>
                    </div>
                </li>

                <li class="list-group-item">
                    When a member's profile is edited <i class="fa fa-exclamation-triangle text-danger"
                                                         aria-hidden="true"
                                                         title="Potentially spammy"></i>
                    <div class="material-switch pull-right">
                        <input type='hidden' value='0' name="slack_alert_updated_member">
                        <input id="slack_alert_updated_member"
                               name="slack_alert_updated_member"
                               type="checkbox" {{ checked($division->settings()->slack_alert_updated_member) }}/>
                        <label for="slack_alert_updated_member" class="label-success"></label>
                    </div>
                </li>

                <li class="list-group-item text-muted">DIVISION</li>

                <li class="list-group-item">
                    When a request is submitted
                    <div class="material-switch pull-right">
                        <input type='hidden' value='0' name="slack_alert_created_request">
                        <input id="slack_alert_created_request"
                               name="slack_alert_created_request"
                               type="checkbox" {{ checked($division->settings()->slack_alert_created_request) }} />
                        <label for="slack_alert_created_request" class="label-success"></label>
                    </div>
                </li>

            </div>
        </div>

    </div>

    {{ csrf_field() }}

    <button type="submit" class="btn btn-success pull-right">Save changes</button>
</form>

