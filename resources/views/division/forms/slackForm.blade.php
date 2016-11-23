<div class="well">
    <fieldset>
        <legend>Slack Integration</legend>

        <form id="slack-settings" method="post"
              action="{{ action('DivisionController@update', $division->abbreviation) }}">

            {{ method_field('PATCH') }}

            <div class="form-group">
                <label for="division_structure" class="control-label">Webhook URL</label>
                <input type="text" id="division_structure" name="division_structure"
                       placeholder="https://hooks.slack.com/services/..."
                       value="{{ $division->settings()->division_structure }}" class="form-control" required/>
                <span class="help-block"><small>Enter the webhook URL you want to post information to.</small></span>
            </div>

            <div class="form-group margin-top-50">

                <div class="panel panel-primary">
                    <div class="panel-heading">Alert Slack when the following events occur...</div>

                    <li class="list-group-item">
                        When a new member is recruited
                        <div class="material-switch pull-right">
                            <input id="slack_alert_recruit" name="slack_alert_recruit" type="checkbox"/>
                            <label for="slack_alert_recruit" class="label-success"></label>
                        </div>
                    </li>

                    <li class="list-group-item">
                        When a member is removed
                        <div class="material-switch pull-right">
                            <input id="slack_alert_removal" name="slack_alert_removal" type="checkbox"/>
                            <label for="slack_alert_removal" class="label-success"></label>
                        </div>
                    </li>

                    <li class="list-group-item">
                        When a member's profile is edited
                        <div class="material-switch pull-right">
                            <input id="slack_alert_profile_edit" name="slack_alert_profile_edit" type="checkbox"/>
                            <label for="slack_alert_profile_edit" class="label-success"></label>
                        </div>
                    </li>

                    <li class="list-group-item">
                        When a removal request is submitted
                        <div class="material-switch pull-right">
                            <input id="slack_alert_removal_request" name="slack_alert_removal_request" type="checkbox"/>
                            <label for="slack_alert_removal_request" class="label-success"></label>
                        </div>
                    </li>

                </div>
            </div>


            <div class="form-group margin-top-50">
                <button type="submit" class="btn btn-default btn-lg pull-right">Save changes</button>
            </div>

            {{ csrf_field() }}
        </form>

    </fieldset>
</div>

