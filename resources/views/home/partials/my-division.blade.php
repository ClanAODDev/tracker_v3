<div class="panel panel-c-accent panel-filled division-header animated bounceInDown"
     style="background-image: url({{ asset('images/headers/' . $myDivision->abbreviation . ".png") }})">
    <div class="panel-heading">

        <h2 class="m-b-none text-uppercase">
            {{ $myDivision->name }}
            @include('division.partials.edit-division-button', ['division' => $myDivision])
        </h2>

        <span class="c-text">{{ $myDivision->members->count() }} MEMBERS</span>
    </div>

    <div class="panel-body">
        @can ('create', App\Member::class)
            <a href="{{ route('recruiting.form', [$myDivision->abbreviation]) }}"
               class="btn btn-default btn-squared">Add New Recruit</a>
        @endcan
        <a href="{{ route('division', $myDivision->abbreviation) }}"
           class="btn btn-default btn-squared">View My Division</a>
    </div>
</div>
