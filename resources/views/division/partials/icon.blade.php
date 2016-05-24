@if (hasDivisionIcon($division->abbreviation))
    <img src="{!! getDivisionIconPath($division->abbreviation) !!}"/>
@else
    <img src="{!! getDivisionIconPath('unknown') !!}"/>
@endif
