<div class="row">
    <div class="col-sm-6">
        <div class="bs-example">
            <h4>{{ $division->locality('squad') }} Details</h4>
            <p>Please provide the details for your {{ $division->locality('squad') }}. Keep in mind the following when assigning a leader:</p>

            <p>Assigning a {{ $division->locality('squad leader') }}</p>
            <ul>
                <li><span class="text-success">DOES</span> move them to this {{ $division->locality('platoon') }}
                    <code>{{ $platoon->name }}</code></li>
                <li><span class="text-success">DOES</span> move them to this {{ $division->locality('squad') }}</li>
                <li><span class="text-success">DOES</span> change their position to
                    <code>{{ $division->locality('squad leader') }}</code></li>
                <br />
                <li><span class="text-danger">DOES NOT</span> change user account access</li>
            </ul>
        </div>
    </div>

    <div class="col-sm-6">
        <div class="panel panel-filled">
            <div class="panel-body">

                <div class="form-group {{ $errors->has('name') ? ' has-error' : null }}">
                    <label for="name" class="form-label">Squad Name</label>
                    {!! Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) !!}
                </div>

                <div class="row">

                    <div class="col-xs-6">
                        <div class="form-group {{ $errors->has('leader_id') ? ' has-error' : null }}">
                            {!! Form::label('leader_id', $division->locality('squad leader') . ':') !!}
                            {!! Form::number('leader_id', null, ['class' => 'form-control'] ) !!}
                        </div>
                    </div>

                    <div class="col-xs-6">
                        <div class="form-group m-t-lg">
                            {!! Form::label('is_tba', 'Leader TBA') !!}
                            <div style="margin-right:5px;float: left;">
                                <input id="is_tba" type="checkbox" {{ (empty($squad->leader)) ? "checked" : null }} />
                            </div>
                        </div>
                    </div>
                </div>

                <a href="{{ route('platoonSquads', [$division->abbreviation, $platoon]) }}"
                   class="btn btn-default">Cancel</a>
                <button type="submit" class="btn btn-success pull-right">Save</button>
            </div>
        </div>

        @include('application.partials.errors')

    </div>
</div>

{{ csrf_field() }}

<script>
    // omit leader field if using TBA
    $("#is_tba").click(function () {
        toggleTBA();
    });

    toggleTBA();

    function toggleTBA() {
        if ($('#is_tba').is(':checked')) {
            $("#leader_id").prop("disabled", true).val('');
        } else {
            $("#leader_id").prop("disabled", false)
        }
    }
</script>