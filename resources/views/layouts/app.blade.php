<!DOCTYPE html>
<html>
<head>
    <title>AOD | Tracker v3</title>
    @include('layouts.header')
</head>
<body>

<!-- modal for tools -->
<div class="modal viewPanel fade">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="viewer fadeIn animate"></div>
        </div>
    </div>
</div>

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


</body>
</html>
