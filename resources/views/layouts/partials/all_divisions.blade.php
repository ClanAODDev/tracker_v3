<div class="panel panel-primary">
    <div class="panel-heading">
        <strong>All Divisions</strong>
        <div class="badge pull-right">{{ $divisions->count() }}</div>
    </div>

    <div class="panel-body">
        <div class='list-group'>
            <div class="hidden-xs hidden-sm">@include('layouts.partials.divisions_two_column')</div>
            <div class="hidden-md hidden-lg">@include('layouts.partials.divisions_one_column')</div>
        </div>

    </div>
</div>