@if (hasDivisionIcon($division->abbreviation))
    <img src="{!! getDivisionIconPath($division->abbreviation) !!}" />
@else
    <img src="{!! asset('images/icons/large/tracker.png') !!}"
         class="unknown-icon"
         title="No division icon defined"
    />
@endif
