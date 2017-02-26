<!DOCTYPE html>
<html>
<head>
    <title>AOD | Tracker v3</title>
    @include('layouts.header')
</head>
<body>

<div id="wrap">

    @include('layouts.partials.nav.base')

    <div class="main-body">
        <div class="container">
            @include('flash::message')
            @yield('content')
        </div>
    </div>

</div>



@include('layouts.footer')

@yield('footer_scripts')

</body>
</html>
