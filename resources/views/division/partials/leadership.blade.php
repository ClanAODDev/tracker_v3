<div class="division-section animate-fade-in-up" style="animation-delay: 0.25s">
<h3 class="division-section-title">Leadership</h3>
<hr />

<div class="row">
    @forelse($divisionLeaders as $leader)
        <div class="col-md-4">
            <a href="{{ route('member', $leader->getUrlParams()) }}" class="panel panel-filled panel-c-danger">
                <div class="panel-body leader-card-body">
                    @if($leader->getDiscordAvatarUrl())
                        <img src="{{ $leader->getDiscordAvatarUrl() }}" alt="{{ $leader->name }}" class="leader-avatar leader-avatar-md">
                    @endif
                    <div class="leader-card-text">
                        <h4 class="m-b-none">{!! $leader->present()->rankName !!}</h4>
                        <small>{{ $leader->position->getLabel() }}</small>
                    </div>
                    <span class="leader-card-icon"><i class="pe pe-2x pe-7s-shield text-muted"></i></span>
                </div>
            </a>
        </div>
    @empty
        <div class="col-md-12">
            <div class="panel panel-filled panel-c-accent">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        No leadership assigned
                    </h4>
                    <span class="slight">See clan leadership for assistance with assignments</span>
                </div>
            </div>
        </div>
    @endforelse
</div>
</div>