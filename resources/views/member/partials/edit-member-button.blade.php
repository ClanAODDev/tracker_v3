<div class="btn-group pull-right text-capitalize">
    <button data-toggle="dropdown" class="member-actions btn btn-default dropdown-toggle" aria-expanded="true">
        <i class="fa fa-wrench text-accent"></i> Actions <span
                class="caret"></span></button>
    <ul class="dropdown-menu">

        @can('update', $member)
            <li>
                <a href="{{ route('editMember', $member->clan_id) }}"> Edit member</a>
            </li>

            @if ($member->user)
                @can('update', $member->user)
                    <li>
                        <a href="{{ route('editUser', $member->clan_id) }}"> Edit account</a>
                    </li>
                @endcan

                @if (auth()->user()->isRole('admin') && !(session('impersonating')))
                    @unless($member->user->id === auth()->user()->id)
                        <li>
                            <a href="{{ route('impersonate', $member->user) }}"> Impersonate User</a>
                        </li>
                    @endunless
                @endif

            @else
                <li class="disabled">
                    <a href="#" class="text-muted" disabled> No account</a>
                </li>
            @endif
            <li class="divider"></li>
        @endcan

        <li>
            <a href="{{ doForumFunction([$member->clan_id,], 'pm') }}" target="_blank">Send forum PM</a>
        </li>

        <li>
            <a href="{{ doForumFunction([$member->clan_id], 'forumProfile') }}" target="_blank">View forum profile</a>
        </li>

        @can ('manageIngameHandles', $member)
            <li>
                <a href="{{ route('member.edit-handles', $member->clan_id) }}">
                    Manage Ingame Handles
                </a>
            </li>
        @endcan

        @can ('managePartTime', $member)
            <li>
                <a href="{{ route('member.edit-part-time', $member->clan_id) }}">
                    Manage Part-Time
                </a>
            </li>
        @endcan

        @can('update', $member)
            <li class="divider"></li>
            <li>
                <a href="{{ route('member.confirm-reset', $member->clan_id) }}"> Reset Assignments</a>
            </li>
        @endcan
    </ul>
</div>

