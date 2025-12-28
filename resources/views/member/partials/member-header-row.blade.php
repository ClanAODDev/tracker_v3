<tr>
    <th data-col="checkbox" class='bulk-select-col no-sort'><input type='checkbox' id='select-all-members' title='Select All'></th>
    <th data-col="rank-id" class='col-hidden'><strong>Rank Id</strong></th>
    <th data-col="last-login" class='col-hidden'><strong>Last Login Date</strong></th>
    <th data-col="member"><strong>Member</strong></th>
    <th data-col="rank" class='no-search text-center hidden-xs'><strong>Rank</strong></th>
    @if(isset($platoon))
        <th data-col="assignment" class='text-center hidden-xs hidden-sm'><strong>{{ $division->locality('Squad') }}</strong></th>
    @else
        <th data-col="assignment" class='text-center hidden-xs hidden-sm'><strong>{{ $division->locality('Platoon') }}</strong></th>
    @endif
    <th data-col="joined" class='text-center hidden-xs hidden-sm'><strong>Joined</strong></th>
    <th data-col="discord-activity" class='text-center hidden-xs'><strong>Discord Activity</strong></th>
    <th data-col="last-promoted" class='text-center hidden-xs'><strong>Last Promoted</strong></th>
    <th data-col="inactivity-reminder" class='text-center hidden-xs hidden-sm no-search'><strong>Inactivity Reminder</strong></th>
    <th data-col="tags" class='no-search hidden-xs hidden-sm'><strong>Tags</strong></th>
    <th data-col="handle" class="col-hidden">Handle</th>
    <th data-col="posts" class="col-hidden">Posts</th>
    <th data-col="member-id" class="col-hidden">Member Id</th>
    <th data-col="discord-activity-date" class='col-hidden'><strong>Discord Activity Dates</strong></th>
    <th data-col="tag-ids" class='col-hidden'><strong>Tag IDs</strong></th>
    <th data-col="reminder-date" class='col-hidden'><strong>Reminder Date</strong></th>
</tr>