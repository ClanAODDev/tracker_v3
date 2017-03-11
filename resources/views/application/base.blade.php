<!DOCTYPE html>
<html>
<head>
    <title>AOD | Tracker v3</title>
    @include('application.header')
</head>


@if (Auth::check())
    <body>
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
