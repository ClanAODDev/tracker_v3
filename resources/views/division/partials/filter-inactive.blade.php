<div class="panel panel-filled m-b-xl panel-c-info">
    <div class="panel-body">
        <div class="row m-t-xs">
            <form method="get">
                <div class="col-md-3">
                    <h4><i class="fa fa-filter text-info"></i> Filter by {{ $division->locality('platoon') }}</h4>
                </div>
                <div class="col-md-4">

                    <select name="platoon" id="platoon" class="form-control"
                            onChange="top.location.href=this.options[this.selectedIndex].value">
                        <option value="">None selected</option>
                        @foreach ($division->platoons as $platoon)
                            <option {{ ($request->platoon && $request->platoon->id == $platoon->id ? 'selected' : null) }} value="{{ route('division.inactive-members', [$division->abbreviation, $platoon->id]) }}">{{ $platoon->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('division.inactive-members', $division->abbreviation) }}"
                       class="btn btn-default">Reset</a>
                </div>
            </form>
        </div>

    </div>
</div>