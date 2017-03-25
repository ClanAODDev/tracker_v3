<div class="panel panel-c-accent panel-filled">
    <div class="panel-heading">

        <img src="{{ getDivisionIconPath($myDivision->abbreviation) }}"
             class="pull-right" />

        <h2 class="m-b-none text-uppercase">
            {{ $myDivision->name }}
            @include('division.partials.edit-division-button', ['division' => $myDivision])
        </h2>

        <span class="c-text">{{ $myDivision->members->count() }} MEMBERS</span>
    </div>

    <div class="panel-body">
        <a href="{{ route('division', $myDivision->abbreviation) }}"
           class="btn btn-default btn-squared">Add New Recruit</a>
        <a href="{{ route('division', $myDivision->abbreviation) }}"
           class="btn btn-default btn-squared">View My Division</a>
    </div>
</div>
