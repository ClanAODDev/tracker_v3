<div class="col-md-3">
    <h5 class="grabbable"><i class="fa fa-drag-handle text-muted"></i>
        <strong>{{ $squad->name or "Untitled" }}</strong>
        @if ($squad->leader)
            <small>{{ $squad->leader->present()->rankName }}</small>
        @else
            <small>TBA</small>
        @endif
        <span class="pull-right badge badge-default count">{{ count($squad->members) }}</span>
    </h5>
    <hr />
    <ul class="sortable" data-squad-id="{{ $squad->id }}">
        @foreach ($squad->members as $member)
            <li class="list-group-item grabbable" data-member-id="{{ $member->id }}"><i class="fa fa-drag-handle text-muted pull-right"></i><span class="no-select">{{ $member->present()->rankName }}</span></li>
        @endforeach
    </ul>
</div>