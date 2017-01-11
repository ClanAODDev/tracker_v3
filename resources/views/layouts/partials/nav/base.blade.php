<nav class="navbar navbar-default navbar-nav navbar-fixed-top">

    <div class="container">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ action('AppController@index') }}"><img src='{!! asset('images/logo.svg') !!}' class='pull-left'/><strong
                        class='logo'>AOD</strong>
                <small>Tracker <sup>v3</sup></small>
            </a>
        </div>

        @if (Auth::check())
            @include('layouts.partials.nav.authed')
        @endif

    </div>
</nav>
