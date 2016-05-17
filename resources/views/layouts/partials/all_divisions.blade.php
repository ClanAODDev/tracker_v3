<div class="panel panel-default">
    <div class="panel-heading"><i class="fa fa-gamepad fa-lg pull-right text-muted"></i> <strong>All Gaming
            Divisions</strong> ({{ $divisions->count() }})
    </div>

    <div class="panel-body">
        <div class='list-group'>
            <div class="hidden-xs hidden-sm">@include('layouts.partials.divisions_two_column')</div>
            <div class="hidden-md hidden-lg">@include('layouts.partials.divisions_one_column')</div>
        </div>

    </div>
</div>