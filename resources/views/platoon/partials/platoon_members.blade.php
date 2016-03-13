<div class="panel panel-primary">
    <div class="panel-heading">Members</div>
    <div class="list-group">
        @foreach($platoon->members as $member)
            <li class="list-group-item">
                {{ $member->name }}
            </li>
        @endforeach
    </div>
</div>