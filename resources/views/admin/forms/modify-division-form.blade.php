<div class="row">
    <div class="col-md-6">
        <h4>Update {{ $division->name }}</h4>
        <div>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ad incidunt magni minus, nihil quae qui sapiente similique! A aliquam consequuntur, est eum expedita, iure laboriosam modi, quasi quo rerum voluptatum?</p>
            <p>Cum delectus eligendi excepturi harum iusto laborum nam natus possimus quis vitae. Aliquam asperiores aspernatur consequatur delectus dolor earum est, eum ex fugiat iste maxime minus nihil odit quis voluptates?</p>
            <p>Atque debitis facilis laudantium nulla porro, vitae? Alias, asperiores at cumque deleniti doloribus earum eligendi eos est excepturi, facere itaque libero nesciunt nostrum odio possimus quibusdam ullam unde ut veritatis.</p>
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
                            {!! Form::text('abbreviation', null, ['class' => 'form-control', 'required' => 'required']) !!}
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