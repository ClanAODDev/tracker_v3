<div class="panel panel-filled">

    <div class="panel-body">

        @if (count($members))
            @foreach ($members as $member)
                <a href="{{ route('member', $member->clan_id) }}" class="panel panel-filled">
                    <div class="panel-body">
                        <span class="c-white">{{ $member->present()->rankName }}</span>
                    </div>
                </a>
            @endforeach
        @else
            <li class="text-muted list-group-item">No results found.</li>
        @endif

    </div>
</div>
