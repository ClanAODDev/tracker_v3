<div class="panel panel-filled">
    <div class="panel-body">
        <div class="row">

            <div class="col-md-6">
                {{ $helpText }}
            </div>

            <div class="col-md-6">
                <input type="hidden" value="{{ $division->id }}" name="division" />

                <h4>Details</h4>

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name"
                                   class="control-label">{{ $division->locality($type) }} Name</label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name') }}" class="form-control" required />
                        </div>

                        <div class="form-group">
                            {!! Form::label('is_tba', 'Leader TBA') !!}
                            <div style="margin-right:5px;float: left;">{!! Form::checkbox('is_tba') !!}</div>
                            <p>
                                <small class="slight">If no leader can be assigned</small>
                            </p>
                        </div>

                    </div>

                    <div class="col-md-6">
                        <div class="form-group {{ $errors->has('leader') ? ' has-warning' : null }}">
                            <label for="leader"
                                   class="control-label">{{ $leader }}</label>

                            <input type="text" id="leader" name="leader" placeholder="AOD Member ID"
                                   value="{{ old('leader') }}" class="form-control" />

                            @if ($errors->has('leader'))
                                <span class="help-block">{{ $errors->first('leader') }}</span>
                            @endif
                        </div>
                    </div>

                </div>

                {{ csrf_field() }}

            </div>
        </div>

        <button type="submit" class="btn btn-default pull-right">Create</button>
    </div>

</div>

<script>
    // omit leader field if using TBA
    $("#is_tba").click(function () {
        if ($('#is_tba').is(':checked')) {
            $("#leader").prop("disabled", true);
        } else {
            $("#leader").prop("disabled", false)
        }
    });
</script>