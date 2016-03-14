<div class="panel panel-primary">
    <div class="panel-heading">Members</div>
    <div class="list-group">
        @foreach($platoon->members as $member)
            <a href="{{ action('MemberController@show', [$member->clan_id]) }}" class="list-group-item">
                {{ $member->name }}
            </a>
        @endforeach
    </div>
</div>