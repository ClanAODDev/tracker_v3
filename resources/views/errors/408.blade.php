@extends('application.base-tracker')

@section('content')

    <div class="error-page-wrapper">
        <div class="error-page">
            <div class="error-page__header">
                <div class="error-page__icon">
                    <img src="/images/logo_v2.svg" alt="AOD">
                </div>
                <h1 class="error-page__title">No Primary Division</h1>
                <p class="error-page__subtitle">You are no longer associated with a primary division</p>
            </div>

            <div class="error-page__body">
                <p class="error-page__intro">
                    <strong>Returning to AOD?</strong><br>
                    If you're a former member looking to rejoin, please reach out to us:
                </p>

                <div class="error-page__options">
                    <a href="https://discord.gg/clanaod" class="error-page__option" target="_blank">
                        <div class="error-page__option-icon error-page__option-icon--discord">
                            <i class="fab fa-discord"></i>
                        </div>
                        <div class="error-page__option-text">
                            <strong>Join Discord</strong>
                            <span>Get with one of our recruiters</span>
                        </div>
                        <i class="fa fa-external-link error-page__option-arrow"></i>
                    </a>

                    <a href="https://clanaod.net/forums" class="error-page__option" target="_blank">
                        <div class="error-page__option-icon error-page__option-icon--forums">
                            <i class="fa fa-comments"></i>
                        </div>
                        <div class="error-page__option-text">
                            <strong>Visit Forums</strong>
                            <span>Browse division discussions</span>
                        </div>
                        <i class="fa fa-external-link error-page__option-arrow"></i>
                    </a>
                </div>

                <p class="error-page__note">
                    If you believe this is an error, please speak with your intended division leadership.
                </p>

                @if(session('impersonating'))
                    <p class="error-page__note">You appear to be impersonating. Try refreshing the page.</p>
                @endif
            </div>

            <div class="error-page__footer">
                <a href="{{ route('logout') }}" class="btn btn-default btn-block">
                    <i class="fa fa-sign-out"></i> Log Out
                </a>
            </div>
        </div>
    </div>

@endsection
