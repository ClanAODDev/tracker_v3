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
                {!! Form::text('abbreviation', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="form-group {{ $errors->has('handle_id') ? ' has-error' : null }}">
                <label for="handle_id" class="form-label">Primary Handle</label>
                {!! Form::select('handle_id', $handles, null, ['class' => 'form-control']) !!}
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

    @include ('admin.forms.define-leadership-form')

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
</div>