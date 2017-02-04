<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="UTF-8">

<link href='http://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900' rel='stylesheet' type='text/css'>

<link href="{{ asset('favicon.ico') }}" type="image/x-icon" rel="Shortcut Icon"/>

{!! Charts::assets() !!}

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css"/>
<link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/font-awesome.css') }}"/>
<link rel="stylesheet" href="{{ asset('vendor/animate.css/animate.css') }}"/>
<link rel="stylesheet" href="{{ asset('vendor/toastr/toastr.min.css') }}"/>
<link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.css') }}"/>

<!-- App styles -->
<link rel="stylesheet" href="{{ asset('css/pe-icons/pe-icon-7-stroke.css') }}"/>
<link rel="stylesheet" href="{{ asset('css/pe-icons/helper.css') }}"/>
<link rel="stylesheet" href="{{ asset('css/stroke-icons/style.css') }}"/>

<link rel="stylesheet" href="{{ asset('css/style.css') }}">

<script src="{{ asset('vendor/pacejs/pace.min.js') }}"></script>
<script src="{{ asset('vendor/toastr/toastr.min.js') }}"></script>

<script>
    toastr.options = {
        "debug": false,
        "newestOnTop": false,
        "positionClass": "toast-bottom-right",
        "closeButton": true,
        "progressBar": true,
    }
</script>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    window.Laravel = <?php echo json_encode([
        'csrfToken' => csrf_token(),
        'appPath' => route('index'),
    ]); ?>
</script>
