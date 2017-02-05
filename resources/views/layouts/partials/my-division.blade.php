<div class="col-sm-12">
    <a href="{{ route('division', $myDivision->abbreviation) }}" class="panel panel-filled panel-c-accent">
        <div class="panel-body">
            <h2 class="m-b-none text-uppercase">
                <img src="{{ getDivisionIconPath($myDivision->abbreviation) }}"
                     class="pull-right"/>
                {{ $myDivision->name }}
            </h2>
            <span>{{ $myDivision->members->count() }} MEMBERS</span>
        </div>
    </a>
</div>