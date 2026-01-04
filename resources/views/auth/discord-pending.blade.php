@extends('application.base-tracker')

@section('content')

    <div class="container-center md">

        <div class="text-center m-b-lg animate-fade-in-up">
            <div class="auth__logo-row m-b-md">
                <i class="fab fa-discord auth__discord-icon"></i>
                <span class="auth__plus">+</span>
                <img src="{{ asset('images/aod-logo.png') }}" alt="AOD" class="auth__aod-logo">
            </div>
            <h2 class="auth__header m-b-xs">ClanAOD Registration</h2>
            <p class="text-muted">Welcome, <strong class="c-white">{{ auth()->user()->discord_username }}</strong></p>
        </div>

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

        <div class="text-center m-t-lg animate-fade-in-up auth__footer">
            <small class="text-muted">Already a member?</small><br>
            <a href="{{ route('logout') }}" class="btn btn-sm btn-default m-t-xs auth__subtle-btn">
                Sign out and use forum login
            </a>
        </div>

    </div>

@endsection
