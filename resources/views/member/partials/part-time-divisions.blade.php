@if($partTimeDivisions->count() > 0)
    <h4 class="m-t-xl">
        Part-Time Divisions
        @can('managePartTime', $member)
            @if($member->id === auth()->user()->member_id)
                <a href="{{ route('filament.profile.pages.part-time-divisions') }}"
                   class="btn btn-default btn-xs"><i class="fa fa-cog"></i> Manage</a>
            @else
                <a href="{{ route('filament.mod.resources.members.edit', $member) }}#part-time-divisions"
                   class="btn btn-default btn-xs"><i class="fa fa-cog"></i> Manage</a>
            @endif
        @endcan
    </h4>
    <hr/>
    <div class="division-chips">
        @foreach($partTimeDivisions as $division)
            <a href="{{ route('division', $division->slug) }}" class="division-chip">
                <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}" class="division-chip-icon">
                <span class="division-chip-name">{{ $division->name }}</span>
                <span class="division-chip-date">{{ $division->pivot->created_at->format('M Y') }}</span>
            </a>
        @endforeach
    </div>
@endif
