<div class="navbar-collapse collapse">

    <form class="navbar-form navbar-right" role="search">
        <div class="form-group has-feedback">
            <input type='text' class='form-control' id='member-search' placeholder='Search for a player...'/>
            <span id="searchclear" class="fa fa-times-circle fa-2x text-muted"></span>
            <div id='member-search-results' class='scroll'></div>
        </div>
    </form>

    <ul class="nav navbar-nav navbar-left">

        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">User CP<span class="caret"></span></a>

            <ul class="dropdown-menu" role="menu">
                <li class="dropdown-submenu">
                    <a href="{{ route('member', Auth::user()->member->clan_id) }}">{{ Auth::user()->name }}</a>
                    <ul class="dropdown-menu">
                        <li class="dropdown-header">ACCESS ROLES</li>
                        @if (Auth::user()->developer)
                            <li class="disabled">
                                <a href="#" disabled>Developer</a>
                            </li>
                        @endif

                        <li class="disabled"><a href="#" disabled>{{ ucwords(Auth::user()->role->label) }}</a></li>
                        <li class="disabled"><a href="#" disabled>{{ Auth::user()->member->position->name }}</a></li>
                    </ul>
                <li class="divider"></li>
                </li>

                <li><a href="#" data-toggle="pill" class="settings-btn"> Settings</a></li>
                <li><a href="{{ Auth::user()->member->AODProfileLink }}"
                       target="_blank"> Forum profile</a></li>
                <li><a href="help/" role="button">Help</a></li>
                <li class="divider"></li>
                <li><a href="{{ url('/logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out pull-right"></i> Logout</a>
                </li>

            </ul>
        </li>

        @if (Auth::user()->isRole('admin') || Auth::user()->isDeveloper())
            <li><a href="{{ route('admin') }}">Admin CP</a></li>
        @endif

    </ul>
</div>
