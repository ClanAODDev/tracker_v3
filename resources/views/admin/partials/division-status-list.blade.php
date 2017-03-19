<div class="panel panel-filled">
    <div class="table-responsive">
        <table class="table table-hover">
            @foreach($divisions as $division)
                <tr>
                    <td>
                        <label for="{{ $division->abbreviation }}" style="cursor:pointer;">
                            {{ $division->name }}
                        </label>
                    </td>
                    <td>
                        <div class="material-switch pull-right">
                            <input type='hidden' value='0' name="divisions[{{ $division->abbreviation }}]">
                            <input id="{{ $division->abbreviation }}" name="divisions[{{ $division->abbreviation }}]"
                                   type="checkbox" {{ checked($division->active) }} />

                        </div>
                    </td>
                </tr>
            @endforeach

        </table>
    </div>
</div>