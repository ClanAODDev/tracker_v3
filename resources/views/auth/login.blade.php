@extends('application.base')

@section('content')

    <div class="container-center">

        <div class="view-header">
            <div class="header-icon">
                <i class="pe page-header-icon pe-7s-unlock"></i>
            </div>
            <div class="header-title">
                <h3>AOD Tracker</h3>
                <small>
                    Please enter your credentials.
                </small>
            </div>
        </div>

        <div class="panel panel-filled">
            <div class="panel-body">
                <form method="POST" action="{{ url('/login') }}">
                    {!! csrf_field() !!}

                    <div class="form-group {{ $errors->has('email') ? 'has-error' : '' }}">
                        <label class="control-label" for="email">E-mail</label>
                        <input type="text" name="email" id="email" class="form-control">
                        @if ($errors->has('email'))
                            <span class="help-block small">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="form-group {{ $errors->has('password') ? ' has-error' : '' }}">
                        <label class="control-label" for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control">
                        @if ($errors->has('password'))
                            <span class="help-block small">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="remember" id="remember"> <label for="remember">Remember Me</label>
                    </div>
                    <div>
                        <button class="btn btn-accent">Login</button>
                        <div class="btn-group pull-right">
                            <a class="btn btn-default" href="{{ url('/register') }}">Register</a>
                            <a class="btn btn-default" href="{{ url('/password/reset') }}">Forgot</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
