<h3 class="division-section-title">General <span class="text-muted">Sergeants</span> <span style="font-size:10px;font-weight:400;letter-spacing:0;margin-left:6px;opacity:0.5;">{{ $generalSergeants->count() }}</span></h3>
<hr/>

<div class="row">
    @forelse ($generalSergeants as $member)
        <div class="col-md-4">
            <a href="{{ route('member', $member->getUrlParams()) }}" class="panel panel-filled">
                <div class="panel-body leader-card-body">
                    @if($member->getDiscordAvatarUrl())
                        <img src="{{ $member->getDiscordAvatarUrl() }}" alt="{{ $member->name }}" class="leader-avatar leader-avatar-md">
                    @else
                        <span class="rank-dot" style="background-color: {{ $member->rank->getColorHex() }}; width: 36px; height: 36px; flex-shrink: 0;"></span>
                    @endif
                    <div class="leader-card-text">
                        <h4 class="m-b-none">{{ $member->rank->getAbbreviation() }} {{ $member->name }}</h4>
                        <small>{{ $member->position->getLabel() }}</small>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-md-12">
            <p class="text-muted" style="font-size: 12px;">No general sergeants in this division.</p>
        </div>
    @endforelse
</div>
