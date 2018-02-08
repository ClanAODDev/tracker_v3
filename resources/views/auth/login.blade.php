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
                    Please enter your <strong>AOD Forum Credentials</strong>.
                </small>
            </div>
        </div>

        @include('application.partials.errors')

        <div class="panel panel-filled">
            <div class="panel-body">
                <form method="POST" action="{{ url('/login') }}">
                    {!! csrf_field() !!}

                    <div class="form-group">
                        <label class="control-label" for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="checkbox" name="remember" id="remember"> <label for="remember">Remember Me</label>
                    </div>

                    <div class="text-center">
                        <button class="btn btn-accent btn-block">Login</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection
