@can ('delete', $squad)
    <h4>Squad History</h4>
    @include ('activity.list', ['activity' => $squad->activity])
@endcan