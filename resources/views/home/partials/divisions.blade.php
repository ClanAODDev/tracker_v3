@foreach($divisions as $division)
    {{-- style="animation-delay: {{ $loop->index/8 }}s;" --}}
    <div class="col-md-4 col-sm-6">
        <a href="{{ route('division', $division->abbreviation) }}" class="panel panel-filled">
            <div class="panel-body">
                <h4 class="m-b-none text-uppercase">
                    <img src="{{ getDivisionIconPath($division->abbreviation, 'medium') }}"
                         class="pull-right" />
                    {{ $division->name }}
                </h4>
                <span class="small">{{ $division->members_count }} MEMBERS</span>
            </div>
        </a>
    </div>
@endforeach