<div class="panel panel-filled">
    <div class="panel-heading">
        <strong>Part-Time Divisions</strong>
    </div>
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
