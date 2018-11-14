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

    <tr>
        <td>
            <label for="slack_alert_member_approved">
                When a member request is approved
            </label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_member_approved">
            <input id="slack_alert_member_approved"
                   name="slack_alert_member_approved"
                   type="checkbox" {{ checked($division->settings()->slack_alert_member_approved) }} />
        </td>
    </tr>

    <tr>
        <td>
            <label for="slack_alert_member_denied">
                When a member request is denied
            </label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_member_denied">
            <input id="slack_alert_member_denied"
                   name="slack_alert_member_denied"
                   type="checkbox" {{ checked($division->settings()->slack_alert_member_denied) }} />
        </td>
    </tr>

</table>