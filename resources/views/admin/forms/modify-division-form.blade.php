<div class="row">
    <div class="col-md-6">
        <h4>Update {{ $division->name }}</h4>
        <div>
            <p>Administrators can rename, disable, or modify divisions on the tracker. For continuity purposes, division abbreviations cannot be changed after a division is created.</p>
            <p>Marking a division inactive will:</p>
            <ul>
                <li>Stop data syncing from the AOD forums for that division</li>
                <li>Remove the division from the tracker listing</li>
                <li>Prevent non-admin users from accessing the division</li>
            </ul>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel panel-filled">
            {!! Form::model($division, ['method' => 'patch', 'route' => ['adminUpdateDivision', $division->abbreviation]]) !!}
            <div class="panel-body">
                {{-- name, abbreviation --}}
                <div class="row">
                    <div class="col-xs-6">
                        <div class="form-group {{ $errors->has('name') ? ' has-error' : null }}">
                            <label for="name" class="form-label">Division Name</label>
                            {!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                    </div>
                    <div class="col-xs-6">
                        <div class="form-group {{ $errors->has('abbreviation') ? ' has-error' : null }}">
                            <label for="abbreviation" class="form-label">Abbreviation</label>
                            {!! Form::text('abbreviation', null, ['class' => 'form-control', 'disabled' => 'disabled']) !!}
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group {{ $errors->has('description') ? ' has-error' : null }}">
                            <label for="description" class="form-label">Description</label>
                            {!! Form::text('description', null, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="form-group">
                            <label for="active" class="form-label m-l-sm">Active</label>
                            {!! Form::hidden('active', false) !!}
                            {!! Form::checkbox('active', 1, null, ['id' => 'active', 'class' => 'pull-left']) !!}
                        </div>
                    </div>
                </div>

                <a href="{{ route('admin') }}#divisions" class="btn btn-default">Cancel</a>
                {!! Form::submit('Save Division', ['class' => 'btn btn-default pull-right']) !!}

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>