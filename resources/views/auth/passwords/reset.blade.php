@extends('application.base')

@section('content')
    <div class="container-center">

        <div class="view-header">
            <div class="header-icon">
                <i class="pe page-header-icon pe-7s-refresh-2"></i>
            </div>
            <div class="header-title">
                <h3>AOD Tracker</h3>
                <small>
                    Password reset form
                </small>
            </div>
        </div>

        <div class="panel panel-filled">
            <div class="panel-body">
                <form class="form" role="form" method="POST" action="{{ url('/password/reset') }}">
                    {!! csrf_field() !!}

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        <label class="control-label">E-Mail Address</label>

                        <input type="email" class="form-control" name="email" value="{{ old('email') }}">

                        @if ($errors->has('email'))
                            <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                        @endif
                    </div>

                    <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                        <label class="control-label">Password</label>

                        <input type="password" class="form-control" name="password">

                        @if ($errors->has('password'))
                            <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                        @endif
                    </div>

                    <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                        <label class="control-label">Confirm Password</label>

                        <input type="password" class="form-control" name="password_confirmation">

                        @if ($errors->has('password_confirmation'))
                            <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                        @endif

                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-btn fa-refresh"></i> Reset Password
                    </button>
                </form>
            </div>
        </div>
@endsection
