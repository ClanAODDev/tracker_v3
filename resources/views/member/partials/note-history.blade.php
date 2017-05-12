<div class="panel panel-filled">
    <div class="panel-heading">Note History</div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table">
                @foreach ($note->activity as $event)
                    <tr>
                        @include("activity.types.{$event->name}")
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
