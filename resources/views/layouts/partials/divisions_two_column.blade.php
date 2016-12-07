{{-- Chunking the divisions into a two column --}}
@foreach ($divisions->chunk(ceil($divisions->count() / 2)) as $chunk)
    <div class="col-md-6 list-group">
        @foreach ($chunk as $division)

            @if ($division->isActive())
                <a href="{{ route('division', [$division->abbreviation]) }}"
                   class="list-group-item"
                   style="padding-bottom: 18px;">

                    <span class="pull-left" style="margin-right: 20px; vertical-align: middle;">
                        @include('division.partials.icon')
                    </span>

                    <h4 class="list-group-item-heading hidden-xs hidden-sm">
                        <strong>{{ $division->name }}</strong>
                    </h4>

                    <p class="list-group-item-text text-muted">{{ $division->description }}</p>
                    <h5 class="pull-right text-muted big-num-main count-animated">{{ $division->members->count() }}</h5>
                </a>
            @endif

        @endforeach
    </div>
@endforeach