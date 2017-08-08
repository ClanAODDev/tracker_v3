@extends('application.base')

@section('content')
    <div class="container-center lg">

        <div class="view-header">
            <div class="header-icon">
                <i class="pe page-header-icon pe-7s-add-user"></i>
            </div>
            <div class="header-title">
                <h3>AOD Tracker</h3>
                <small>
                    Create a new account using your forum username and a valid email
                </small>
            </div>
        </div>

        @if (count($errors))
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </div>
        @endif
        <div class="panel panel-filled">
            <div class="panel-body">

                <form role="form" method="POST" action="{{ url('/register') }}">
                    {!! csrf_field() !!}

                    <div class="row">

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }} col-lg-6">
                            <label class="control-label" for="name">Username</label>
                            <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                            <span class="help-block small">Must match forum name. Do not include "AOD_"</span>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }} col-lg-6">
                            <label class="control-label" for="email">E-mail</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                            <span class="help-block small">Must be a valid emailt</span>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }} col-lg-6">
                            <label class="control-label" for="email">Password</label>
                            <input type="password" class="form-control" name="password">
                            <span class="help-block small">Use something other than your forum password</span>
                        </div>

                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }} col-lg-6">
                            <label class="control-label" for="password_confirmation">Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirmation">
                            <span class="help-block small">Confirm your chosen password</span>
                        </div>

                    </div>

                    <div class="pull-right">
                        <button type="submit" class="btn btn-accent">Register</button>
                        <a href="{{ route('login') }}" class="btn btn-default">Login</a>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
