<!DOCTYPE html>
<html>
<head>
    <title>AOD | Tracker v3</title>
    @include('layouts.header')
</head>


@if (Auth::check())
    <body>
    {!! Toastr::message() !!}
    <div class="wrapper">
        <nav class="navbar navbar-default navbar-fixed-top">
            @include('layouts.partials.primaryHeader')
        </nav>
        <aside class="navigation">
            @include('layouts.partials.navigation')
        </aside>

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

@include('layouts.footer')

@yield('footer_scripts')

</html>
