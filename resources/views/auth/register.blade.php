@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-primary">
                    <div class="panel-heading">Register</div>
                    <div class="panel-body">

                        <form role="form" method="POST" action="{{ url('/register') }}">
                            {!! csrf_field() !!}

                            <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">

                                <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                                       placeholder="Username">

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                    <strong>{{ $errors->first('name') }}</strong>
                                </span>
                                @endif

                            </div>

                            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">

                                <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                                       placeholder="Email Address">
                                @if ($errors->has('email'))
                                    <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                                @endif
                            </div>

                            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                <input type="password" class="form-control" name="password" placeholder="Password">
                                @if ($errors->has('password'))
                                    <span class="help-block">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                                @endif
                            </div>

                            <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                <input type="password" class="form-control" name="password_confirmation"
                                       placeholder="Confirm Password">
                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                            </span>
                                @endif
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-btn fa-user"></i>Register
                                </button>

                            </div>
                        </form>
                    </div>
                    <div class="panel-footer text-muted">
                        <p>
                            <small>Your username should be the one you use for the AOD Forums, <strong>without the AOD
                                    prefix</strong>.
                            </small>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
