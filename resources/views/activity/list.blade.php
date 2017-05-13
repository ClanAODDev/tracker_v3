<div class="table-responsive">
    <table class="table">
        @foreach ($activity as $event)
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
        @endforeach
    </table>
</div>