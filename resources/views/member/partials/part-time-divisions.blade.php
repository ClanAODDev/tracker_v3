@if($partTimeDivisions->count() > 0)
    <h4 class="m-t-xl">
        Part-Time Divisions
        @can('managePartTime', $member)
            @if($member->id === auth()->user()->member_id)
                <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#part-time-divisions-modal">
                    <i class="fa fa-cog"></i> Manage
                </button>
            @else
                <a href="{{ route('filament.mod.resources.members.edit', $member) }}#part-time-divisions"
                   class="btn btn-default btn-xs"><i class="fa fa-cog"></i> Manage</a>
            @endif
        @endcan
    </h4>
    <hr/>
    <div class="division-cards">
        @foreach($partTimeDivisions as $division)
            <a href="{{ route('division', $division->slug) }}" class="division-card">
                <div class="division-card-logo">
                    <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}">
                </div>
                <div class="division-card-info">
                    <div class="division-card-name">{{ $division->name }}</div>
                    <div class="division-card-meta">
                        <span class="division-card-since">Since {{ $division->pivot->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif
