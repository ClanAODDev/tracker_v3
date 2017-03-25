<fieldset>
    <legend><i class="fa fa-cubes"></i> {{ $actionText }} {{ $division->locality('squad') }}
        <button type="submit" class="btn btn-success pull-right btn-xs">Save</button>
    </legend>

    <div class="row">
        <div class="col-sm-6 hidden-xs">
            <p>Provide the details for your new {{ $division->locality('squad') }} here. When assigning a leader, the
                tracker will automatically assign them to the new squad in the current platoon, and set their position
                to {{ $division->locality('squad leader') }}.</p>
            <p>If no leader is available, leave it blank and it will be marked
                <code>TBA</code>. You can update it later.</p>

            <p>Leaders can only be assigned to a single {{ $division->locality('squad') }} and they must belong to the
                current division.</p>

            <small class="text-muted">
                <sup>1</sup>{{ str_plural($division->locality('squad')) }} designated as general population may be used
                to track recruited members left over when a {{ $division->locality('squad leader') }} leaves his/her
                position, but it cannot have a leader or a name, and there can only be one in
                a {{ $division->locality('platoon') }}.
            </small>
            <p></p>
        </div>
        <div class="col-sm-6">
            <div class="form-group {{ $errors->has('leader_id') ? ' has-error' : null }}">
                <div class="form-group">
                    {!! Form::label('leader_id', $division->locality('squad leader') . ':') !!}
                    {!! Form::number('leader_id', null, ['class' => 'form-control'] ) !!}
                </div>

                <span class="help-block">
                    @if ($errors->has('leader_id'))
                        {{ $errors->first('leader_id') }}
                    @endif
                </span>
            </div>

            <div class="form-group">
                <input type='hidden' name='gen_pop' value='0' />
                {!! Form::label('gen_pop', 'General Population') !!}
                <span style="margin-right:5px;float: left;">{!! Form::checkbox('gen_pop') !!}</span><sup>1</sup>
            </div>
        </div>
    </div>

    {{ csrf_field() }}

</fieldset>
