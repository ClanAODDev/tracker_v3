@can ('delete', $platoon)
    <h4>Platoon History</h4>
    @include ('activity.list', ['activity' => $platoon->activity])
@endcan