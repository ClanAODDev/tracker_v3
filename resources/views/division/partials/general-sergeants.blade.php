<h3 class="division-section-title">General <span class="text-muted">Sergeants</span> <span style="font-size:10px;font-weight:400;letter-spacing:0;margin-left:6px;opacity:0.5;">{{ $generalSergeants->count() }}</span></h3>
<hr/>
<div class="panel panel-filled">
    <div>

    @forelse ($generalSergeants as $member)
        <a href="{{ route('member', $member->getUrlParams()) }}" class="list-group-item">
            <h4 class="list-group-item-heading">
                @if($member->getDiscordAvatarUrl())
                    <img src="{{ $member->getDiscordAvatarUrl() }}" alt="{{ $member->name }}" class="leader-avatar">
                @endif
                <strong>{{ $member->rank->getAbbreviation() }} {{ $member->name }}</strong></h4>
            <p class="list-group-item-text text-muted"><?php echo $member->position->getLabel(); ?></p>
        </a>
    @empty
        <li class="list-group-item text-muted">This division has no general sergeants</li>
    @endforelse
</div>