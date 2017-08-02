<table class="table table-hover table-striped table-bordered">
    <tr>
        <td>
            <label for="slack_alert_created_member">When a new member is recruited</label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_created_member">
            <input id="slack_alert_created_member" name="slack_alert_created_member"
                   type="checkbox" {{ checked($division->settings()->slack_alert_created_member) }} />
        </td>
    </tr>

    <tr>
        <td>
            <label for="slack_alert_removed_member">
                When a member is removed
            </label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_removed_member">
            <input id="slack_alert_removed_member"
                   name="slack_alert_removed_member"
                   type="checkbox" {{ checked($division->settings()->slack_alert_removed_member) }} />
        </td>
    </tr>

    {{--<tr>
        <td>
            <label for="slack_alert_created_request">
                When a request is submitted
            </label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_created_request">
            <input id="slack_alert_created_request" disabled="disabled"
                   name="slack_alert_created_request"
                   type="checkbox" {{ checked($division->settings()->slack_alert_created_request) }} />
        </td>
    </tr>--}}

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