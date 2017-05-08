{{-- only show leader form when creating new divisions --}}
@unless (isset($division))
    <div class="row">

        <div class="col-xs-8">
            {!! Form::label('leader', 'Search') !!}
            <input type="text" class="form-control" name="leader" id="leader" autocomplete="off" />
            <i class="fa fa-search pull-right" style="position: absolute; right: 20px; top: 35px;"></i>
        </div>

        <div class="col-xs-4">
            <div class="form-group {{ $errors->has('leader_id') ? ' has-error' : null }}">
                {!! Form::label('leader_id', 'Commander:') !!}
                {!! Form::number('leader_id', null, ['class' => 'form-control'] ) !!}
            </div>
        </div>
    </div>
@endunless