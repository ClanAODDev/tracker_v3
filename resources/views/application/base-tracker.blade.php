<!DOCTYPE html>
<html>
<head>
    @yield('metadata')
    <title>AOD | Tracker v3</title>
    @include('application.header')
    @include('application.partials.console')
</head>

@if (Auth::check() && Auth::user()->member && Auth::user()->member->division)
    @php
        $userSettings = Auth::user()->settings;
        $bodyClasses = collect([
            session('primary_nav_collapsed') === true ? 'nav-toggle' : null,
            ($userSettings['disable_animations'] ?? false) ? 'no-animations' : null,
            ($userSettings['mobile_nav_side'] ?? 'right') === 'left' ? 'mobile-nav-left' : null,
        ])->filter()->implode(' ');
    @endphp
    <body class="{{ $bodyClasses }}">
    {!! Toastr::message() !!}

    <div class="wrapper">
        <canvas id="canvas" style="position: fixed; z-index:-1"></canvas>
        <nav class="navbar navbar-default navbar-fixed-top">
            @include('application.partials.primaryHeader')
        </nav>
        <aside class="navigation">
            @include('application.partials.navigation')
        </aside>

        <div class="mobile-nav-overlay"></div>
        <aside class="mobile-nav-drawer">
            <div class="mobile-nav-header">
                <span class="mobile-nav-title">Menu</span>
                <button class="mobile-nav-close"><i class="fa fa-times"></i></button>
            </div>
            <div class="mobile-nav-content">
                @include('application.partials.mobile-navigation')
            </div>
        </aside>

        @include('application.partials.settings-slideover')

        <section class="search-results closed text-center"></section>
        <section class="content">
            @include('application.partials.alert')
            @yield('content')
        </section>
    </div>
    </body>

@else
    <body class="blank">
    <div class="wrapper">
        <section class="content">
            @yield('content')
        </section>
    </div>
    </body>
@endif

@include('application.footer')

@yield('footer_scripts')

</html>
