<div class="btn-group pull-right">
    <button data-toggle="dropdown" class="member-actions btn btn-default dropdown-toggle" aria-expanded="true">
        <i class="fa fa-wrench text-accent"></i> Actions <span
                class="caret"></span></button>
    <ul class="dropdown-menu">

        @can('update', $member)
            <li>
                <a href="{{ route('editMember', $member->clan_id) }}"> Edit member</a>
            </li>
            <li class="divider"></li>
        @endcan

        @can('create', App\Note::class)
            <li>
                <a href="#" class="btn-add-note" data-toggle="modal"
                   data-target="#create-member-note">Add member note</a>
            </li>
        @endcan

        <li>
            <a href="{{ doForumFunction([$member->clan_id,], 'pm') }}" target="_blank">Send forum PM</a>
        </li>

        <li>
            <a href="{{ doForumFunction([$member->clan_id], 'forumProfile') }}" target="_blank">View forum profile</a>
        </li>
    </ul>
</div>