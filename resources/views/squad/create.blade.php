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
        <div class="col-sm-6">
            <ul class="nav nav-pills pull-right">
                <li>
                    <a href="{{ route('platoon', [$division->abbreviation, $platoon->id]) }}">
                        <i class="fa fa-cube fa-lg"></i>
                        {{ ucwords($division->locality('platoon')) }}
                    </a>
                </li>

                <li>
                    <a href="{{ route('platoonSquads', [$division->abbreviation, $platoon->id]) }}">
                        <i class="fa fa-cubes fa-lg"></i>
                        {{ str_plural($division->locality('squad')) }}
                    </a>
                </li>

                @can('update', $platoon)
                    <li class="pull-right active">
                        <a href="#"><i class="fa fa-plus fa-lg"></i><span class="hidden-xs hidden-sm">Create {{ $division->locality('squad') }}</span>
                        </a>
                    </li>
                @endcan
            </ul>
        </div>
    </div>
    <hr/>

    <form id="create-squad" method="post" class="well"
          action="{{ route('savePlatoon', $division->abbreviation) }}">
        <fieldset>
            <legend><i class="fa fa-sliders"></i> {{ $division->locality('squad') }} Details
                <button type="submit" class="btn btn-success pull-right btn-xs">Create</button>
            </legend>

            <div class="row">
                <div class="col-sm-6">
                    <p>Provide the details for your new {{ $division->locality('squad') }} here. When assigning a leader, if they do not already have the {{ $division->locality('squad leader') }} position, it will be updated for them. Any existing assignment will be cleared.</p>
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
                    <div class="form-group">
                        <label for="name" class="control-label">{{ $division->locality('squad') }} Name</label>
                        <input type="text" id="name" name="name"
                               value="{{ old('name') }}" class="form-control" required/>
                    </div>

                    <div class="form-group {{ $errors->has('leader') ? ' has-error' : null }}">
                        <label for="leader" class="control-label">{{ $division->locality('squad leader') }}</label>
                        <input type="text" id="leader" name="leader" placeholder="AOD Member ID"
                               value="{{ old('leader') }}" class="form-control"/>

                        <span class="help-block">
                        @if ($errors->has('leader'))
                                {{ $errors->first('leader') }}
                            @endif
                        </span>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" value="{{ old('general-population') }}" name="gen-pop">General Population
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