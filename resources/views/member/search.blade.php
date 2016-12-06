@if (count($members))
    @foreach ($members as $member)
        <a href="{{ action('MemberController@show', $member->clan_id) }}" class="list-group-item">
            <strong>{{ $member->present()->rankName }}</strong>
        </a>
    @endforeach
@else
    <li class="text-muted list-group-item">No results found.</li>
@endif
