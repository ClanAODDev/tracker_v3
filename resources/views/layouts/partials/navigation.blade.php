<ul class="nav luna-nav">

    <li class="active">
        <a href="index.html">Dashboard</a>
    </li>

    <li>
        <a href="#">Documentation</a>
    </li>

    <li>
        <a href="#monitoring" data-toggle="collapse" aria-expanded="false">
            User CP<span class="sub-nav-icon"> <i class="stroke-arrow"></i> </span>
        </a>

        <ul id="monitoring" class="nav nav-second collapse">
            <li><a href="{{ route('member', Auth::user()->member->clan_id) }}">{{ Auth::user()->name }}</a></li>
            <li><a href="usage.html"> Settings</a></li>
            <li><a href="activity.html"> Forum Profile</a></li>
            <li><a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa fa-sign-out pull-right"></i> Logout</a>
            </li>
        </ul>
    </li>

    <li>
        <a href="#uielements" data-toggle="collapse" aria-expanded="false">
            Divisions<span class="sub-nav-icon"> <i class="stroke-arrow"></i> </span>
        </a>
        <ul id="uielements" class="nav nav-second collapse">
            @foreach (App\Division::active()->get() as $division)
                <li><a href="#">{{ $division->name }}</a></li>
            @endforeach
        </ul>
    </li>
</ul>