<h5>Part-Time</h5>
<div class="panel panel-filled">
    <div class="panel-body">
        @forelse ($member->partTimeDivisions as $division)
            <a href="{{ route('division', $division->abbreviation) }}">
                <img src="{{ getDivisionIconPath($division->abbreviation) }}"
                     title="{{ $division->name }}" />
            </a>
        @empty
            <span class="text-muted">None</span>
        @endforelse
    </div>
</div>
