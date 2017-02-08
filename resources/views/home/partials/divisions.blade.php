@foreach($divisions as $division)
    @if($division->id != $myDivision->id)
        <div class="col-md-6">
            <a href="{{ route('division', $division->abbreviation) }}" class="panel panel-filled">
                <div class="panel-body">
                    <h4 class="m-b-none text-uppercase">
                        <img src="{{ getDivisionIconPath($division->abbreviation, 'medium') }}"
                             class="pull-right"/>
                        {{ $division->name }}
                    </h4>
                    <span class="small">{{ $division->members_count }} MEMBERS</span>
                </div>
            </a>
        </div>
    @endif
@endforeach