@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="panel panel-primary">
                <div class="panel-heading text-left">Log In</div>
                <div class="panel-body">

                    <form role="form" method="POST" action="{{ url('/login') }}">
                        {!! csrf_field() !!}

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email Address" />

                            @if ($errors->has('email'))
                            <span class="help-block">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                            @endif

                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">


                            <input type="password" class="form-control" name="password" placeholder="Password" />

                            @if ($errors->has('password'))
                            <span class="help-block">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                            @endif

                        </div>

                        <div class="form-group">

                            <div class="col-xs-6">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember"> Remember Me
                                    </label>
                                </div>
                            </div>

                            <div class="col-xs-6">
                                <button type="submit" class="btn btn-primary pull-right">
                                    <i class="fa fa-btn fa-sign-in"></i>Login
                                </button>
                            </div>

                        </div>
                    </form>

                </div>
                <div class="panel-footer text-muted"><small><a class="btn btn-link btn-xs" href="{{ url('/register') }}">Register</a></small> | <small><a class="btn btn-link btn-xs" href="{{ url('/password/reset') }}">Forgot Password</a></small></div>
            </div>
        </div>
    </div>
</div>
@endsection
