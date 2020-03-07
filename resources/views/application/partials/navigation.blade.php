<ul class="nav luna-nav">
    <li class="{{ set_active(['home', '/']) }}">
        <a href="{{ route('home') }}">Dashboard</a>
    </li>

    <li>
        <a href="https://clanaod.net/forums">AOD Forums</a>
    </li>

    <li class="{{ set_active('members/' . auth()->user()->member->clan_id) }}">
        <a href="#user-cp" data-toggle="collapse" aria-expanded="false">
            {{ auth()->user()->name }}
            @if (session('impersonating'))
                <i class="fa fa-user-secret text-accent" title="Currently Impersonating"></i>
            @endif
            @if (auth()->user()->isDeveloper())
                <i class="fa fa-user-shield text-danger" title="Dev mode enabled"></i>
            @endif
            <span class="sub-nav-icon"> <i class="stroke-arrow"></i> </span>
        </a>

        <ul id="user-cp" class="nav nav-second collapse">
            <li class="no-select">
                <a href="#">
                    <small class="text-muted text-uppercase slight">
                        Role: <strong>{{ auth()->user()->role->name }}</strong>
                    </small>
                </a>
            </li>

            @if (session('impersonating'))
                <li>
                    <a href="{{ route('end-impersonation') }}">
                        <strong class="text-danger">End Impersonation</strong>
                        <i class="fa fa-user-secret "></i>
                    </a>
                </li>
            @endif

            <li>
                <a href="{{ route('member', auth()->user()->member->getUrlParams()) }}">
                    Member Profile
                </a>
            </li>

            <li>
                <a href="{{ doForumFunction([auth()->user()->member->clan_id], 'forumProfile') }}">Forum Profile</a>
            </li>

            <li>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout <i class="fa fa-lock text-muted"></i>
                </a>
            </li>

        </ul>
    </li>

    @can('create', App\Member::class)
        <li class="{{ set_active('recruit') }}">
            <a href="{{ route('recruiting.initial') }}">Add New Recruit</a>
        </li>
    @endcan

    <li class="nav-category">
        Members
    </li>

    <li class="{{ set_active('search/members') }} visible-xs">
        <a href="{{ route('memberSearch') }}">Search</a>
    </li>

    <li class="{{ set_active('sergeants') }}">
        <a href="{{ route('sergeants') }}">Sergeants</a>
    </li>

    <li class="{{ set_active('reports/*') }}">
        <a href="#reports" data-toggle="collapse" aria-expanded="false">
            Clan Reports
            <span class="sub-nav-icon"> <i class="stroke-arrow"></i> </span>
        </a>

        <ul id="reports" class="nav nav-second {{ request()->is('reports/*') ? 'expanded' : 'collapse' }}">

            <li class="{{ set_active('reports/clan-census') }}">
                <a href="{{ route('reports.clan-census') }}">Clan Census Data</a>
            </li>
            <li class="{{ set_active('reports/clan-ts-report') }}">
                <a href="{{ route('reports.clan-ts-report') }}">TS Misconfiguration</a>
            </li>
            <li class="{{ set_active('reports/outstanding-inactives') }}">
                <a href="{{ route('reports.outstanding-inactives') }}">Outstanding Inactives</a>
            </li>
            @if (auth()->user()->isRole('admin'))
                <li class="{{ set_active('reports/division-turnover') }}">
                    <a href="{{ route('reports.division-turnover') }}">Division Turnover</a>
                </li>
            @endif
        </ul>
    </li>

    @if(Auth::user()->isRole('admin'))
        <li class="nav-category">
            Admin
        </li>
        <li class="{{ set_active(['admin', 'admin/divisions/create', 'admin/handles/create']) }}">
            <a href="/admin">Admin CP</a>
        </li>
        <li class="{{ set_active('admin/member-requests') }}">
            <a href="{{ route('admin.member-request.index') }}">
                Member Requests <span
                        class="badge text-info">{{ \App\MemberRequest::pending()->pastGracePeriod()->count() }}</span>
            </a>
        </li>

    @endif

    <li class="nav-category">
        Application
    </li>

    <li class="{{ set_active('help') }}">
        <a href="{{ route('help') }}">Documentation</a>
    </li>

    <li class="{{ set_active('changelog') }}">
        <a href="{{ route('changelog') }}">Changelog <span class="label label-success">NEW</span></a>
    </li>
</ul>

{{--back to top--}}
<span id="top-link-block" class="hidden">
    <a href="#top" class="btn btn-default smooth-scroll"
       onclick="$('html,body').animate({scrollTop:0},'slow'); return false;">
        <i class="glyphicon glyphicon-chevron-up"></i> Back to Top
    </a>
</span><!-- /top-link-block -->
