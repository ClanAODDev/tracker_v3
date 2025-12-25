<div class="members-table-toggle text-center">
    <div class="btn-group column-toggles">
        <span class="toggle-label">Columns</span>
        <a class="toggle-vis btn btn-default" href="#" data-column="rank">Rank</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="assignment">{{ isset($platoon) ? $division->locality('Squad') : $division->locality('Platoon') }}</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="joined">Join Date</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="discord-activity">Discord Activity</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="last-promoted">Last Promoted</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="tags">Tags</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="handle">Handle</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="posts">Posts</a>
    </div>
    @if(isset($includeParttimers))
    <div class="btn-group filter-toggles">
        <span class="toggle-label">Filter</span>
        <a class="filter-btn btn {{ $includeParttimers ? 'active' : '' }}"
           href="{{ request()->fullUrlWithQuery(['parttimers' => $includeParttimers ? null : 1]) }}">
            <i class="fa fa-users"></i> Part-Timers
        </a>
    </div>
    @endif
</div>