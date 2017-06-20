<div class="panel-body">
    {{-- name, abbreviation --}}
    <div class="row">
        <div class="col-xs-6">
            <div class="form-group {{ $errors->has('label') ? ' has-error' : null }}">
                <label for="label" class="form-label">Handle Label</label>
                {!! Form::text('label', null, ['class' => 'form-control', 'required' => 'required']) !!}
            </div>
        </div>

        <div class="col-xs-6">
            <div class="form-group {{ $errors->has('type') ? ' has-error' : null }}">
                <label for="type" class="form-label">Handle Slug</label>
                {!! Form::text('type', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="form-group {{ $errors->has('comments') ? ' has-error' : null }}">
                <label for="comments" class="form-label">Comments</label>
                {!! Form::text('comments', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="form-group {{ $errors->has('url') ? ' has-error' : null }}">
                <label for="url" class="form-label">Handle URL</label>
                {!! Form::text('url', null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>


    <a href="{{ route('admin') }}#handles" class="btn btn-default">Cancel</a>
    {!! Form::submit('Save Handle', ['class' => 'btn btn-default pull-right']) !!}
</div>