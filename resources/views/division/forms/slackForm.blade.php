<fieldset>
    <legend><i class="fa fa-slack"></i> Slack Integration  <button type="submit" class="btn btn-success pull-right btn-xs">Save changes</button></legend>

    <form id="slack-settings" method="post"
          action="{{ action('DivisionController@update', $division->abbreviation) }}">

        {{ method_field('PATCH') }}

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <div class="panel panel-primary">
                        <div class="panel-heading">Alert Slack when the following events occur...</div>

                        <li class="list-group-item text-muted">MEMBER</li>

                        <li class="list-group-item">
                            When a new member is recruited
                            <div class="material-switch pull-right">
                                <input id="slack_alert_created_member" name="slack_alert_created_member" type="checkbox" {{ checked($division->settings()->slack_alert_created_member) }} />
                                <label for="slack_alert_created_member" class="label-success"></label>
                            </div>
                        </li>

                        <li class="list-group-item">
                            When a member is removed
                            <div class="material-switch pull-right">
                                <input id="slack_alert_removed_member" name="slack_alert_removed_member" type="checkbox" {{ checked($division->settings()->slack_alert_removed_member) }} />
                                <label for="slack_alert_removed_member" class="label-success"></label>
                            </div>
                        </li>

                        <li class="list-group-item">
                            When a member's profile is edited
                            <div class="material-switch pull-right">
                                <input id="slack_alert_updated_member" name="slack_alert_updated_member" type="checkbox" {{ checked($division->settings()->slack_alert_updated_member) }}/>
                                <label for="slack_alert_updated_member" class="label-success"></label>
                            </div>
                        </li>

                        <li class="list-group-item text-muted">DIVISION</li>

                        <li class="list-group-item">
                            When a request is submitted
                            <div class="material-switch pull-right">
                                <input id="slack_alert_created_request" name="slack_alert_created_request" type="checkbox" {{ checked($division->settings()->slack_alert_created_request) }} />
                                <label for="slack_alert_created_request" class="label-success"></label>
                            </div>
                        </li>

                    </div>
                </div>
            </div>

            <div class="col-md-6">

                <div class="form-group">
                    <label for="slack_webhook_url" class="control-label">Webhook URL</label>
                    <input type="text" id="slack_webhook_url" name="slack_webhook_url"
                           placeholder="https://hooks.slack.com/services/..."
                           value="{{ $division->settings()->slack_webhook_url }}" class="form-control" required/>
                    <span class="help-block"><small>Enter the webhook URL you want to post information to.</small></span>
                </div>
            </div>

        </div>

        {{ csrf_field() }}
    </form>

</fieldset>
