<div class="panel panel-c-accent panel-filled">
    <div class="panel-heading">
        <h2 class="m-b-none text-uppercase">
            <img src="{{ getDivisionIconPath($myDivision->abbreviation) }}"
                 class="pull-right" />
            {{ $myDivision->name }}
        </h2>
        <span class="c-text">{{ $myDivision->members->count() }} MEMBERS</span>
    </div>

    <div class="panel-body">
        {{-- Report alerts regarding the division here --}}
        <p class="text-muted slight">No requests for your division at this time</p>
    </div>

    <div class="panel-footer btn-group">

        <a href="{{ route('division', $myDivision->abbreviation) }}"
           class="btn btn-default btn-squared">View My Division</a>
        <a href="{{ route('editDivision', $myDivision->abbreviation) }}"
           class="btn btn-default btn-squared">Manage Division Settings</a>
    </div>
</div>
