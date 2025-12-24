<div class="members-table-toggle text-center">
    <div class="btn-group column-toggles">
        <span class="toggle-label">Columns</span>
        <a class="toggle-vis btn btn-default" href="#" data-column="4">Rank</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="5">Join Date</a>
        <a class="toggle-vis btn btn-default" data-column="6">Discord Activity</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="7">Last Promoted</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="8">Tags</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="9">Handle</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="10">Posts</a>
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