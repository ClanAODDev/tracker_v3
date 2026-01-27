@extends('application.base-tracker')

@section('content')

    <div class="discord-pending-wrapper">

        <div class="text-center m-b-lg animate-fade-in-up">
            <div class="auth__logo-row m-b-md">
                <i class="fab fa-discord auth__discord-icon"></i>
                <span class="auth__plus">+</span>
                <img src="{{ asset('images/aod-logo.png') }}" alt="AOD" class="auth__aod-logo">
            </div>
            <h2 class="auth__header m-b-xs">ClanAOD Registration</h2>
            <p class="text-muted">Welcome, <strong class="c-white">{{ auth()->user()->discord_username }}</strong></p>
        </div>

        @if (! auth()->user()->date_of_birth || ! auth()->user()->forum_password)
            <div class="panel panel-filled animate-fade-in-up auth__panel">
                <div class="auth__pattern"></div>
                <div class="panel-body">
                    <p class="auth__intro text-center m-b-lg">
                        Before we continue, we need a few more details.
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <p class="m-b-none">{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('auth.discord.register') }}" method="POST" id="dob-form">
                        @csrf
                        <div class="form-group">
                            <label>What game are you interested in playing?</label>
                            <p class="help-block text-muted m-b-sm">Optional - helps us connect you with the right division</p>
                            <div class="division-select-grid">
                                @foreach($divisions as $division)
                                    <label class="division-select-item">
                                        <input type="radio" name="division_id" value="{{ $division->id }}">
                                        <div class="division-select-card">
                                            <img src="{{ $division->getLogoPath() }}" alt="{{ $division->name }}" class="division-select-logo">
                                            <span class="division-select-name">{{ $division->name }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}" required>
                            <p class="help-block text-muted">You must be at least 13 years old to join.</p>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" required minlength="8">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password" required>
                                </div>
                            </div>
                        </div>
                        <p class="help-block text-muted m-t-none">This will be your forum account password.</p>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">
                                Continue <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            <div class="panel panel-filled animate-fade-in-up auth__panel">
                <div class="auth__pattern"></div>
                <div class="panel-body">
                    <p class="auth__intro text-center m-b-lg">
                        Connect with one of our recruiters to complete the process!
                    </p>

                    <div class="row auth__steps">
                        <div class="col-sm-5 text-center m-b-md auth__step">
                            <div class="auth__step-badge">
                                <span class="auth__step-number">1</span>
                            </div>
                            <p class="auth__step-text m-b-none">Join our Discord server</p>
                        </div>
                        <div class="col-sm-2 text-center hidden-xs auth__divider">
                            <div class="auth__divider-line"></div>
                        </div>
                        <div class="col-sm-5 text-center m-b-md auth__step">
                            <div class="auth__step-badge">
                                <span class="auth__step-number">2</span>
                            </div>
                            <p class="auth__step-text m-b-none">
                                Post in <code class="auth__channel">#recruiting</code>
                            </p>
                        </div>
                    </div>

                    <div class="text-center m-t-md">
                        <a href="https://discord.gg/clanaod" target="_blank" class="btn btn-primary auth__discord-btn">
                            <i class="fab fa-discord"></i> Join Discord
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <div class="text-center m-t-lg animate-fade-in-up auth__footer">
            <small class="text-muted">Already a member?</small><br>
            <a href="{{ route('logout') }}" class="btn btn-sm btn-default m-t-xs auth__subtle-btn">
                Sign out and use forum login
            </a>
        </div>

    </div>

@endsection
