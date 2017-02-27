@extends('application.base')

<!-- Main Content -->
@section('content')

    <div class="container-center animated slideInDown">

        <div class="view-header">
            <div class="header-icon">
                <i class="pe page-header-icon pe-7s-id"></i>
            </div>
            <div class="header-title">
                <h3>Reset password</h3>
                <small>
                    Please enter your email to reset your password.
                </small>
            </div>
        </div>

        <div class="panel panel-filled">
            <div class="panel-body">
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <form class="form" role="form" method="POST" action="{{ url('/password/email') }}">
                    {!! csrf_field() !!}
                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        <label class="control-label" for="email">Email adress</label>
                        <input type="text" placeholder="example@gmail.com" title="Please enter you username" required=""
                               value="" name="email" id="email" class="form-control">
                        @if ($errors->has('email'))
                            <span class="help-block small">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                        @endif
                    </div>
                    <div>
                        <button type="submit" class="btn btn-accent">
                            <i class="fa fa-btn fa-envelope"></i> Send Password Reset Link
                        </button>
                        <a class="btn btn-default" href="{{ route('login') }}">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
