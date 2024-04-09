
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="UTF-8">

@if(App::environment('develop', 'local'))
    <link href="{{ asset('favicon_dev.ico?v=3.3') }}" type="image/x-icon" rel="Shortcut Icon"/>
@else
    <link href="{{ asset('favicon.ico?v=3.3') }}" type="image/x-icon" rel="Shortcut Icon" />
@endif

<!-- vendor styles -->
<!-- @TODO: bundle CSS resources -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/>
{{--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"/>--}}

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" crossorigin="anonymous">

<link rel="stylesheet" href="{{ asset('vendor/animate.css/animate.css') }}"/>
<link rel="stylesheet" href="{{ asset('vendor/toastr/toastr.min.css') }}"/>
<link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.css') }}"/>

<!-- App styles -->
<link href="https://fonts.googleapis.com/css?family=Lato|Open+Sans" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/pe-icons/pe-icon-7-stroke.css') }}"/>
<link rel="stylesheet" href="{{ asset('css/pe-icons/helper.css') }}"/>
<link rel="stylesheet" href="{{ asset('css/stroke-icons/style.css') }}"/>
<link rel="stylesheet" href="{{ asset('vendor/datatables/datatables.min.css') }}"/>
<link rel="stylesheet" href="{{ asset('css/style.css') }}?v3.2.72">

<script src="{{ asset('js/libs.js?v=2.1') }}"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs/jszip-2.5.0/dt-1.11.3/b-2.1.1/b-html5-2.1.1/b-print-2.1.1/r-2.2.9/sl-1.3.4/datatables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    window.Laravel = <?php echo json_encode([
        'csrfToken' => csrf_token(),
        'appPath' => route('index'),
    ]); ?>
</script>
