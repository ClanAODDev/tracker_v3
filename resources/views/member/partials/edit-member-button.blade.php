<div class="btn-group pull-right">
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
            @endif
            <li class="divider"></li>
        @endcan

        <li>
            <a href="{{ doForumFunction([$member->clan_id,], 'pm') }}" target="_blank">Send forum PM</a>
        </li>

        <li>
            <a href="{{ doForumFunction([$member->clan_id], 'forumProfile') }}" target="_blank">View forum profile</a>
        </li>
    </ul>
</div>