@foreach ($divisions as $division)
    @if ($division->isActive())
        <a href="{{ action('DivisionController@show', [$division->abbreviation]) }}"
           class="list-group-item"
           style="padding-bottom: 18px;">
            
            <span class="pull-left" style="margin-right: 20px; vertical-align: middle;">
                @include('division.partials.icon')
            </span>

            <h4 class="list-group-item-heading hidden-md hidden-lg">
                <strong>{{ strtoupper($division->abbreviation) }}</strong>
            </h4>

            <p class="list-group-item-text text-muted hidden-xs">{{ $division->description }}</p>

            <h5 class="pull-right text-muted big-num-main count-animated">{{ $division->members->count() }}</h5>
        </a>
    @endif
@endforeach
