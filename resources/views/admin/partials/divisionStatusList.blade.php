<table class="table table-striped table-hover">
    @foreach($divisions as $division)
        <tr>
            <td>
                {{ $division->name }}</td>
            <td>
                <div class="material-switch pull-right">
                    <input type='hidden' value='0' name="divisions[{{ $division->abbreviation }}]">
                    <input id="{{ $division->abbreviation }}" name="divisions[{{ $division->abbreviation }}]" type="checkbox" {{ checked($division->active) }} />
                    <label for="{{ $division->abbreviation }}" class="label-success"></label>
                </div>
            </td>
        </tr>
    @endforeach

</table>