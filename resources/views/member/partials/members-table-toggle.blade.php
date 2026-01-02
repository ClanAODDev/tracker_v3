<div class="members-table-toggle text-center">
    <div class="btn-group column-toggles">
        <div class="dropup">
            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">
                <i class="fa fa-columns"></i> Columns <span class="caret"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-right column-toggle-menu">
                <li><a class="toggle-vis" href="#" data-column="rank"><i class="fa fa-check"></i> Rank</a></li>
                <li><a class="toggle-vis" href="#" data-column="assignment"><i class="fa fa-check"></i> {{ isset($platoon) ? $division->locality('Squad') : $division->locality('Platoon') }}</a></li>
                <li><a class="toggle-vis" href="#" data-column="joined"><i class="fa fa-check"></i> Join Date</a></li>
                <li><a class="toggle-vis" href="#" data-column="discord-activity"><i class="fa fa-check"></i> Discord Activity</a></li>
                <li><a class="toggle-vis" href="#" data-column="last-promoted"><i class="fa fa-check"></i> Last Promoted</a></li>
                <li><a class="toggle-vis" href="#" data-column="inactivity-reminder"><i class="fa fa-check"></i> Inactivity Reminder</a></li>
                <li><a class="toggle-vis" href="#" data-column="tags"><i class="fa fa-check"></i> Tags</a></li>
                <li><a class="toggle-vis" href="#" data-column="handle"><i class="fa fa-check"></i> Handle</a></li>
                <li><a class="toggle-vis" href="#" data-column="posts"><i class="fa fa-check"></i> Posts</a></li>
            </ul>
        </div>
    </div>
    @if(isset($includeParttimers))
    <div class="btn-group filter-toggles">
        <a class="filter-btn btn {{ $includeParttimers ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['parttimers' => $includeParttimers ? null : 1]) }}">
            <i class="fa fa-users"></i> Part-Timers
        </a>
    </div>
    @endif
</div>
