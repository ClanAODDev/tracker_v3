@forelse($members as $member)
    <a class="panel" href="{{ route('member', $member->clan_id) }}"
       style="padding-left: 30px; margin-bottom: 0;">
        <div class="panel-body">
            <h4 class="m-b-none">
                {!! $member->present()->rankName !!}
            </h4>
        </div>
    </a>
@empty
    <div class="panel text-muted">
        <div class="panel-body" style="padding-top: 55px; pointer-events: none;">
            <h4 class="text-muted"><i class="fa fa-times-circle"></i> No results using your search criteria</h4>
        </div>
    </div>
@endforelse

@if (count($members))
    <div class="panel" style="padding-left: 30px; margin-bottom: 0;">
        <div class="panel-body">
            <h4 class="m-b-none text-muted text-uppercase slight">
                No more items
            </h4>
        </div>
    </div>
@endif