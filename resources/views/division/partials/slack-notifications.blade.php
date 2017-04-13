<label for="slack-notifications">Slack Notifications</label>
<table class="table table-hover table-striped table-bordered" id="slack-notifications">
    <tr>
        <th colspan="2" class="text-muted text-uppercase">Member</th>
    </tr>

    <tr>
        <td>
            <label for="slack_alert_created_member">When a new member is recruited</label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_created_member">
            <input id="slack_alert_created_member" disabled="disabled"
                   name="slack_alert_created_member"
                   type="checkbox" {{ checked($division->settings()->slack_alert_created_member) }} />
        </td>
    </tr>

    <tr>
        <td>
            <label for="slack_alert_removed_member">
                When a member is removed
                <i class="fa fa-exclamation-triangle text-danger"
                   aria-hidden="true" title="Potentially spammy"></i>
            </label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_removed_member">
            <input id="slack_alert_removed_member"
                   name="slack_alert_removed_member"
                   type="checkbox" {{ checked($division->settings()->slack_alert_removed_member) }} />
        </td>
    </tr>

    <tr>
        <td>
            <label for="slack_alert_updated_member">
                When a member's profile is edited
                <i class="fa fa-exclamation-triangle text-danger"
                   aria-hidden="true" title="Potentially spammy"></i>
            </label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_updated_member">
            <input id="slack_alert_updated_member" disabled="disabled"
                   name="slack_alert_updated_member"
                   type="checkbox" {{ checked($division->settings()->slack_alert_updated_member) }}/>
        </td>
    </tr>

    <tr>
        <th colspan="2" class="text-muted text-uppercase">Division</th>
    </tr>

    <tr>
        <td>
            <label for="slack_alert_created_request">
                When a request is submitted
            </label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_created_request">
            <input id="slack_alert_created_request" disabled="disabled"
                   name="slack_alert_created_request"
                   type="checkbox" {{ checked($division->settings()->slack_alert_created_request) }} /></td>
    </tr>

    <tr>
        <td>
            <label for="slack_alert_division_edited">
                When your division's settings are edited
            </label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_division_edited">
            <input id="slack_alert_division_edited"
                   name="slack_alert_division_edited"
                   type="checkbox" {{ checked($division->settings()->slack_alert_division_edited) }}/>
        </td>
    </tr>

</table>