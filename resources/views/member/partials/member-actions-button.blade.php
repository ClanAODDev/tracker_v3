<div class="btn-group pull-right m-r-md text-capitalize">
    <button data-toggle="dropdown" class="member-actions btn btn-accent dropdown-toggle" aria-expanded="true">
        <i class="fa fa-wrench text-accent"></i> <span class="hidden-xs">Actions</span> <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">

        @can('update', $member)
            <li>
                <a href="{{ route('filament.mod.resources.members.edit', $member) }}">Edit Member</a>
            </li>

            @can('train', auth()->user())
                <li>
                    <a href="{{ route('training.sgt', ['clan_id' => $member->clan_id, 'training']) }}#sgt-duties"
                    >SGT Training</a>
                </li>
            @endcan

            @if ($member->user)
                @can('impersonate', $member->user)
                    <li>
                        <a href="{{ route('impersonate', $member->user) }}"> Impersonate User</a>
                    </li>
                @endcan
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

        @can('update', $member)
            <li class="divider"></li>
            <li>
                <a href="{{ route('member.flag-inactive', $member->clan_id) }}">Flag For Inactivity</a>
            </li>
            @unless(auth()->user()->member?->clan_id === $member->clan_id)
                <li>
                    <a href="#" class="set-activity-reminder-btn" data-url="{{ route('member.set-activity-reminder', $member->clan_id) }}">
                        Mark Sent Reminder
                    </a>
                </li>
            @endunless
        @endcan
    </ul>
</div>

