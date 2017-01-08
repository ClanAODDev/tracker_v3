@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('create-platoon', $division) !!}

    <h2>
        @include('division.partials.icon')

        <strong>{{ $division->name }}</strong>
        <small>Create {{ $division->locality('platoon') }}</small>
    </h2>

    <form id="create-platoon" method="post" class="well"
          action="{{ route('savePlatoon', $division->abbreviation) }}">
        <fieldset>
            <legend><i class="fa fa-cube"></i> {{ $division->locality('platoon') }} Details
                <button type="submit" class="btn btn-success pull-right btn-xs">Create</button>
            </legend>

            <div class="row">
                <div class="col-sm-6">
                    <p>Provide the details for your new {{ $division->locality('platoon') }} here. When assigning a leader, the tracker will automatically assign them to the new platoon, and set their position to {{ $division->locality('platoon leader') }}.</p>
                    <p>If no leader is available, <strong>leave it blank</strong> and it will be marked
                        <code>TBA</code>. You can update it later.</p>
                    <p>Leaders can only be assigned to a single {{ $division->locality('platoon') }} and they must belong to the current division.</p>
                </div>

                <input type="hidden" value="{{ $division->id }}" name="division"/>

                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="name" class="control-label">{{ $division->locality('platoon') }} Name</label>
                        <input type="text" id="name" name="name"
                               value="{{ old('name') }}" class="form-control" required/>
                    </div>

                    <div class="form-group {{ $errors->has('leader') ? ' has-error' : null }}">
                        <label for="leader" class="control-label">{{ $division->locality('platoon leader') }}</label>
                        <input type="text" id="leader" name="leader" placeholder="AOD Member ID"
                               value="{{ old('leader') }}" class="form-control"/>

                        <span class="help-block">
                        @if ($errors->has('leader'))
                                {{ $errors->first('leader') }}
                            @endif
                        </span>

                    </div>
                </div>
            </div>

            {{ csrf_field() }}

        </fieldset>
    </form>

@stop


