@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('squad', $division, $platoon) !!}

    <div class="row">
        <div class="col-sm-6">
            <h2>
                @include('division.partials.icon')
                <strong>{{ $platoon->name }}</strong>
                <small>{{ $division->name }}</small>
            </h2>
        </div>
    </div>

    <form id="create-squad" method="post" class="well margin-top-20"
          action="{{ route('saveSquad', [$division->abbreviation, $platoon->id]) }}">
        <fieldset>
            <legend><i class="fa fa-cubes"></i> New {{ $division->locality('squad') }} Details
                <button type="submit" class="btn btn-success pull-right btn-xs">Create</button>
            </legend>

            <div class="row">
                <div class="col-sm-6 hidden-xs">
                    <p>Provide the details for your new {{ $division->locality('squad') }} here. When assigning a leader, the tracker will automatically assign them to the new squad in the current platoon, and set their position to {{ $division->locality('squad leader') }}.</p>
                    <p>If no leader is available, leave it blank and it will be marked
                        <code>TBA</code>. You can update it later.</p>
                    <p>Leaders can only be assigned to a single {{ $division->locality('squad') }} and they must belong to the current division.</p>
                    <small class="text-muted">
                        <sup>1</sup>{{ str_plural($division->locality('squad')) }} designated as general population may be used to track recruited members left over when a {{ $division->locality('squad leader') }} leaves his/her position, but it cannot have a leader or a name, and there can only be one in a {{ $division->locality('platoon') }}.
                    </small>
                    <p></p>
                </div>

                <input type="hidden" value="{{ $platoon->id }}" name="division"/>

                <div class="col-sm-6">
                    <div class="form-group {{ $errors->has('leader_is') ? ' has-error' : null }}">
                        <label for="leader_is" class="control-label">{{ $division->locality('squad leader') }}</label>
                        <input type="text" id="leader_is" name="leader_is" placeholder="AOD Member ID"
                               value="{{ old('leader_is') }}" class="form-control"/>

                        <span class="help-block">
                        @if ($errors->has('leader_id'))
                                {{ $errors->first('leader_id') }}
                            @endif
                        </span>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" value="{{ old('gen_pop') }}"
                                       name="gen_pop">General Population
                                <sup>1</sup>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            {{ csrf_field() }}

        </fieldset>
    </form>

@stop
