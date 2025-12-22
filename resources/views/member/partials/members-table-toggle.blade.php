<div class="text-center">
    <div class="m-t-md btn-group">
        <div class="btn hidden-xs" style="pointer-events: none">Toggle</div>
        <a class="toggle-vis btn btn-default" href="#" data-column="4">Rank</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="5">Join Date</a>
        <a class="toggle-vis btn btn-default" data-column="6">Discord Activity</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="7">Last Promoted</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="8">Tags</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="9">Handle</a>
        <a class="toggle-vis btn btn-default" href="#" data-column="10">Posts</a>
    </div>
    @if(isset($includeParttimers))
    <div class="m-t-md btn-group m-l-md">
        <div class="btn hidden-xs" style="pointer-events: none">Filter</div>
        <a class="btn {{ $includeParttimers ? 'btn-accent' : 'btn-default' }}"
           href="{{ request()->fullUrlWithQuery(['parttimers' => $includeParttimers ? null : 1]) }}">
            <i class="fa fa-users"></i> Include Part-Timers
        </a>
    </div>
    @endif
</div>