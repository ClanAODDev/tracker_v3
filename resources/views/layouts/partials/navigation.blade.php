<ul class="nav luna-nav">

    <li class="active">
        <a href="index.html">Home</a>
    </li>

    <li>
        <a href="#">Documentation</a>
    </li>

    <li>
        <a href="#user-cp" data-toggle="collapse" aria-expanded="false">
            User CP<span class="sub-nav-icon"> <i class="stroke-arrow"></i> </span>
        </a>

        <ul id="user-cp" class="nav nav-second collapse">
            <li><a href="{{ route('member', Auth::user()->member->clan_id) }}">{{ Auth::user()->name }}</a></li>
            <li><a href="usage.html">Settings</a></li>
            <li><a href="activity.html">Forum Profile</a></li>
            <li><a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            </li>
        </ul>
    </li>

    <li>
        <a href="#divisions" data-toggle="collapse" aria-expanded="false">
            Divisions<span class="sub-nav-icon"> <i class="stroke-arrow"></i> </span>
        </a>
        <ul id="divisions" class="nav nav-second collapse">
            @foreach (App\Division::active()->get() as $division)
                <li><a href="#">{{ $division->name }}</a></li>
            @endforeach
        </ul>
    </li>

    <li>
        <a href="#tools" data-toggle="collapse" aria-expanded="false">
            Tools<span class="sub-nav-icon"> <i class="stroke-arrow"></i> </span>
        </a>
        <ul id="tools" class="nav nav-second collapse">
           <li><a href="#">Thing</a></li>
           <li><a href="#">Thing</a></li>
           <li><a href="#">Thing</a></li>
           <li><a href="#">Thing</a></li>
           <li><a href="#">Thing</a></li>
        </ul>
    </li>
</ul>