<div class="panel panel-c-accent panel-filled division-header"
     style="background-image: url({{ asset('images/headers/' . $myDivision->abbreviation . ".png") }})">
    <div class="panel-heading">

        <h2 class="m-b-none text-uppercase">
            <span style="cursor: pointer;"
                  onclick="window.location.href='{{ route('division', $myDivision->abbreviation) }}'">{{ $myDivision->name }}</span>
            @include('division.partials.edit-division-button', ['division' => $myDivision])
        </h2>

        <span class="c-text">{{ $myDivision->members->count() }} MEMBERS</span>
    </div>

    <div class="panel-body">
        @can ('create', App\Member::class)
            <a href="{{ route('recruiting.form', [$myDivision->abbreviation]) }}"
               class=" btn btn-default m-t-sm ">Add Recruit</a>
        @endcan
        <a href="{{ route('partTimers', $myDivision->abbreviation) }}"
           class=" btn btn-default m-t-sm ">
            Part Timers
        </a>
        @can ('viewDivisionStructure', auth()->user())
            <a href="{{ route('division.structure', $myDivision->abbreviation) }}"
               class=" btn btn-default m-t-sm ">
                Structure
            </a>
        @endcan

        <a href="{{ route('leave.index', $myDivision->abbreviation) }}"
           class=" btn btn-default m-t-sm ">
            Leave
        </a>

            <a href="{{ route('division.inactive-members', $myDivision->abbreviation) }}"
               class=" btn btn-default m-t-sm ">
                Inactive Members
            </a>
    </div>
</div>
