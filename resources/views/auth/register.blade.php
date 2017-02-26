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

                    <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                        <label class="control-label" for="name">Username</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                               placeholder="Username">
                    </div>

                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        <label class="control-label" for="email">E-mail</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                               placeholder="Email Address">
                    </div>

                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                        <label class="control-label" for="email">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Password">
                    </div>

                    <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                        <label class="control-label" for="email">Confirm Password</label>
                        <input type="password" class="form-control" name="password_confirmation"
                               placeholder="Confirm Password">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-accent btn-block">
                            Register
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
@endsection
