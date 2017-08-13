@if(count($flagActivity))

    <h4 class="m-t-xl">Activity Log</h4><hr />
    <div class="panel panel-filled panel-c-accent">
        <div class="panel-body">
            <table class="table table-hover adv-datatable">
                <thead>
                <tr>
                    <th>Action</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($flagActivity as $activity)
                    @if (isset($activity->subject->name))
                        @include('division.partials.inactive-activity-log-entry')
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif