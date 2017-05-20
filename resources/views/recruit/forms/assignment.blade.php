<div class="panel panel-filled">
    <div class="panel-heading text-uppercase">
        <strong>Assignment</strong>
    </div>
    <div class="panel-body">
        <p>Depending on your division's configuration, a {{ $division->locality('platoon') }} and {{ $division->locality('squad') }} assignment may be required.</p>

        <div class="row">
            <div class="col-sm-6 form-group">
                <label for="platoon">{{ $division->locality('platoon') }}</label>
                <select name="platoon" id="platoon" class="form-control">
                    <option value="">Select a platoon...</option>
                    @foreach ($division->platoons as $platoon)
                        <option value="{{ $platoon->id }}">
                            {{ $platoon->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 form-group">
                <label for="squad">{{ $division->locality('squad') }}</label>
                <select name="squad" id="squad" class="form-control" disabled>
                    <option>Select a platoon...</option>
                </select>
            </div>
        </div>
    </div>
</div>