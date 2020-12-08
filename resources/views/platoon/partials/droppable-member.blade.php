<div class="col-md-3">
    <h5 class="grabbable"><i class="fa fa-drag-handle text-muted"></i>
        @if ($squad->leader)
            <small>{{ $squad->leader->present()->rankName }}</small>
        @else
            <small>TBA</small>
        @endif
        <br />
        <div class="m-t-sm">{{ $squad->name ?? "Untitled" }}
            <span class="pull-right badge badge-default count">{{ count($squad->members) }}</span>
        </div>
    </h5>
    <hr />
    <ul class="sortable" data-squad-id="{{ $squad->id }}" style="max-height: 200px; overflow-y: scroll;">
        @foreach ($squad->members as $member)
            <li class="list-group-item grabbable" data-member-id="{{ $member->id }}"><i
                        class="fa fa-drag-handle text-muted pull-right"></i><span
                        class="no-select">{{ $member->present()->rankName }}</span>
                @if ($squad->leader && $squad->leader->clan_id == $member->recruiter_id)
                    <strong style="color: magenta;" title="Direct Recruit"><i
                            class="fa fa-asterisk"></i></strong>
                @endif
            </li>
        @endforeach
    </ul>
</div>