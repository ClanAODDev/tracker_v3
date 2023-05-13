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

    <tr>
        <td>
            <label for="slack_alert_pt_member_removed">
                When a part-time member gets removed by their primary division
            </label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_pt_member_removed">
            <input id="slack_alert_pt_member_removed"
                   name="slack_alert_pt_member_removed"
                   type="checkbox" {{ checked($division->settings()->slack_alert_pt_member_removed) }} />
        </td>
    </tr>

    <tr>
        <td>
            <label for="slack_alert_member_transferred">When a member transfers into {{ $division->name }}</label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_member_transferred">
            <input id="slack_alert_member_transferred" name="slack_alert_member_transferred"
                   type="checkbox" {{ checked($division->settings()->slack_alert_member_transferred) }} />
        </td>
    </tr>

    {{--
    <tr>
        <td>
            <label for="slack_alert_member_recommendation_created">When a division recommendation is created</label>
        </td>
        <td>
            <input type='hidden' value='0' name="slack_alert_member_recommendation_created">
            <input id="slack_alert_member_recommendation_created" name="slack_alert_member_recommendation_created"
                   type="checkbox" {{ checked($division->settings()->slack_alert_member_recommendation_created) }} />
        </td>
    </tr>

    --}}

</table>