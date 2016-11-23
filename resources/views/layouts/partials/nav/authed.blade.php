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
                <li>
                    <a href="{{ action('MemberController@show', Auth::user()->member->clan_id) }}">{{ Auth::user()->name }}
                        <span class="pull-right"></span>
                    </a>
                </li>

                <li class="divider"></li>
                <li class='disabled'><a href='#' disabled>{{ ucwords(Auth::user()->role->label) }}
                        @if (Auth::user()->developer)
                            <i class="fa fa-shield text-danger pull-right"></i>
                        @endif</a></li>
                <li class='divider'></li>

                <li><a href="#" data-toggle="pill" class="settings-btn"> Settings</a></li>
                <li><a href="{{ Auth::user()->member->AODProfileLink }}"
                       target="_blank"> Forum profile</a></li>
                <li><a href="help/" role="button">Help</a></li>
                <li class="divider"></li>
                <li><a href="{{ url('/logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-lock pull-right"></i> Logout</a>
                </li>

            </ul>
        </li>

        @if (Auth::user()->isRole('admin') || Auth::user()->isDeveloper())
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin<span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                    <li class="disabled"><a href="#">API</a></li>
                    <li class="divider"></li>
                    <li><a href={{ action('API\APIController@divisions') }} target="_blank" role="button">Divisions</a>
                    </li>
                    <li><a href={{ action('API\APIController@platoons') }} target="_blank" role="button">Platoons</a>
                    </li>
                    <li><a href={{ action('API\APIController@squads') }} target="_blank" role="button">Squads</a></li>
                </ul>
            </li>
        @endif

    </ul>
</div>
