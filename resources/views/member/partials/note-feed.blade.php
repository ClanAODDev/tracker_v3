@can ('delete', $note)
    <h4>Note History</h4>
    @include ('activity.list', ['activity' => $note->activity])
@endcan