<h4>Part-Time Divisions</h4>
<div class="row">
    @forelse ($member->partTimeDivisions as $division)
        <div class="col-md-4">
            <a href="{{ route('division', $division->abbreviation) }}" class="panel panel-filled">
                <div class="panel-body">
                    <h4 class="text-uppercase">
                        {{ $division->name }}
                        <span class="pull-right division-icon-medium">
                            <img src="{{ getDivisionIconPath($division->abbreviation) }}"
                                 title="{{ $division->name }}" />
                        </span>
                    </h4>
                    <span class="small text-uppercase">Since {{ $division->pivot->created_at->format('M d, Y') }}</span>
                </div>
            </a>
        </div>
    @empty
        <div class="col-md-4">
            <span class="text-muted">Not a part-time member of any divisions</span>
        </div>
    @endforelse
</div>