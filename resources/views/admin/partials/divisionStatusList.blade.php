@foreach($divisions as $division)
    <li class="list-group-item">
        {{ $division->name }} <a href="{{ action('DivisionController@show', $division->abbreviation) }}"><i class="fa fa-arrow-circle-right"></i></a>
        <div class="material-switch pull-right">
            <input type='hidden' value='0' name="divisions[{{ $division->abbreviation }}]">
            <input id="{{ $division->abbreviation }}" name="divisions[{{ $division->abbreviation }}]" type="checkbox" {{ checked($division->active) }} />
            <label for="{{ $division->abbreviation }}" class="label-success"></label>
        </div>
    </li>
@endforeach