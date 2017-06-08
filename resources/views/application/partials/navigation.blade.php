<ul class="nav luna-nav">
    <li class="{{ set_active('home') }}">
        <a href="{{ route('home') }}">Dashboard</a>
    </li>

    <li class="{{ set_active('statistics') }}">
        <a href="{{ route('statistics') }}">Statistics</a>
    </li>

    <li class="{{ set_active('help') }}">
        <a href="{{ route('help') }}">Documentation</a>
    </li>


    @if(Auth::user()->isRole('admin'))
        <li class="{{ set_active('admin') }}">
            <a href="{{ route('admin') }}">Admin CP</a>
        </li>
    @endif

    <li>

        <a href="#user-cp" data-toggle="collapse" aria-expanded="false">
            User CP
            @if (session('impersonating'))
                <i class="fa fa-user-secret text-danger" title="Currently Impersonating"></i>
            @endif

            <span class="sub-nav-icon"> <i class="stroke-arrow"></i> </span>
        </a>

        <ul id="user-cp" class="nav nav-second collapse">

            <li>
                <a href="{{ route('member', auth()->user()->member->clan_id) }}">
                    @if (auth()->user()->isDeveloper())
                        <i class="fa fa-shield text-danger" title="Dev mode enabled"></i>
                    @endif
                    {{ auth()->user()->name }}
                    <small class="text-muted text-uppercase">[{{ auth()->user()->role->name }}]</small>
                </a>
            </li>

            <li>
                <a href="{{ doForumFunction([auth()->user()->member->clan_id], 'forumProfile') }}">Forum Profile</a>
            </li>

            <li>
                <a href="#"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            </li>

            @if (session('impersonating'))
                <li>
                    <a href="{{ route('end-impersonation') }}"><i
                                class="fa fa-user-secret text-danger"></i> End Impersonation </a>
                </li>
            @endif

        </ul>
    </li>

    @if (auth()->user()->role_id > 1)
        <li>
            <a href="#tools" data-toggle="collapse" aria-expanded="false">
                Tools<span class="sub-nav-icon"> <i class="stroke-arrow"></i> </span>
            </a>
            <ul id="tools" class="nav nav-second collapse">
                @can('create', App\Member::class)
                    <li><a href="{{ route('recruiting.initial') }}">Add New Recruit</a></li>
                @endcan
            </ul>
        </li>
    @endif

    <li class="nav-category">
        Members
    </li>

    <li class="{{ set_active('members') }}">
        <a href="{{ route('members') }}">All Members</a>
    </li>

    <li class="{{ set_active('sergeants') }}">
        <a href="{{ route('sergeants') }}">Sergeants</a>
    </li>
</ul>