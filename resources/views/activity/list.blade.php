<div class="table-responsive">
    <table class="table table-hover">
        @forelse ($activity as $event)
            <tr title="{{ $event->created_at }}">
                @if (view()->exists("activity.types.{$event->name}"))
                    @include ("activity.types.{$event->name}")
                @else
                    <td>
                        <i class="fa fa-times-rectangle text-danger"></i>
                        Missing activity template for {{ $event->name }}
                    </td>
                @endif
            </tr>
        @empty
            <tr>
                <td>
                    <i class="fa fa-times-rectangle text-muted"></i>
                    No activity recorded
                </td>
            </tr>
        @endforelse
    </table>
</div>