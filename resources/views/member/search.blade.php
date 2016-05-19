@if (count($members))
    @foreach ($members as $member)
        <a href="{{ action('MemberController@show', $member->clan_id) }}" class="list-group-item">
            <strong>{{ $member->present()->rankName }}</strong>
            @if ($member->primaryDivision)
                <span class="pull-right text-muted">{{ $member->primaryDivision->name }}</span>
            @endif
        </a>
    @endforeach
@else
    <li class="text-muted list-group-item">No results found.</li>
@endif
