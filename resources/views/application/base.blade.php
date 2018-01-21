<!DOCTYPE html>
<html>
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-107795217-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag () {dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'UA-107795217-1');
    </script>

    @yield('metadata')
    <title>AOD | Tracker v3</title>
    @include('application.header')
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
