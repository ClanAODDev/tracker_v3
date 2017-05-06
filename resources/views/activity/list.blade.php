<div class="table-responsive">
    <table class="table">
        @foreach ($activity as $event)
            <tr>
                @include("activity.types.{$event->name}")
            </tr>
        @endforeach
    </table>
</div>