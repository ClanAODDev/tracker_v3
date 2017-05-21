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
            User CP<span class="sub-nav-icon"> <i class="stroke-arrow"></i> </span>
        </a>

        <ul id="user-cp" class="nav nav-second collapse">
            <li><a href="{{ route('member', auth()->user()->member->clan_id) }}">
                    {{ auth()->user()->name }}
                    <small class="text-muted text-uppercase">[{{ auth()->user()->role->name }}]</small>
                </a></li>
            {{--<li><a href="usage.html">Settings</a></li>--}}
            <li><a href="{{ doForumFunction([auth()->user()->member->clan_id], 'forumProfile') }}">Forum Profile</a></li>
            <li><a href="#"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            </li>
        </ul>
    </li>

    <li>
        <a href="#tools" data-toggle="collapse" aria-expanded="false">
            Tools<span class="sub-nav-icon"> <i class="stroke-arrow"></i> </span>
        </a>
        <ul id="tools" class="nav nav-second collapse">
            {{--class="{{ set_active('home') }}"--}}
            <li><a href="{{ route('recruiting.initial') }}">Add New Recruit</a></li>
        </ul>
    </li>
</ul>