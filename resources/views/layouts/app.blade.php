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
{{--
<div id="wrap">

    @include('layouts.partials.nav.base')

    <div class="main-body">
        <div class="container">
            @include('flash::message')
            @yield('content')
        </div>
    </div>

</div>--}}

@can('edit_member')
    <h1>can edit this guy</h1>
@endcan


@include('layouts.footer')

@yield('footer_scripts')

</body>
</html>
