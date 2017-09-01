<!DOCTYPE html>
<html>
<head>
    <title>AOD | Tracker v3</title>
    @include('application.header')
    @yield('metadata')
</head>

@if (Auth::check() && Auth::user()->member->division)
    <body class="{{ session('primary_nav_collapsed') === true ? 'nav-toggle' : null }}">
    {!! Toastr::message() !!}
    <div class="wrapper">
        <nav class="navbar navbar-default navbar-fixed-top">
            @include('application.partials.primaryHeader')
        </nav>
        <aside class="navigation">
            @include('application.partials.navigation')
        </aside>

        <section class="search-results closed text-center"></section>

        <section class="content">
            {{--@if (session('impersonating'))--}}
            {{--@include ('application.partials.impersonation')--}}
            {{--@endif--}}

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
