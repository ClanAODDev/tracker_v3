<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="UTF-8">
<script src="https://use.fontawesome.com/b59371f038.js"></script>

<link href="{{ asset('favicon.ico') }}" type="image/x-icon" rel="Shortcut Icon" />

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.0.23/css/bulma.min.css">

<link rel="stylesheet" type="text/css"
      href="//cdnjs.cloudflare.com/ajax/libs/jquery-powertip/1.2.0/css/jquery.powertip.css">
<link href="//fonts.googleapis.com/css?family=Source+Sans+Pro" rel='stylesheet' type='text/css'>

{!! Charts::assets() !!}

<link href="{{ asset('/css/libs/sweetalert2.css') }}" rel="stylesheet">
{{--<link href="{{ asset('/css/app.css') }}" rel="stylesheet">--}}

<link rel="stylesheet" type="text/css"
      href="https://cdn.datatables.net/v/dt/dt-1.10.13/b-1.2.4/b-colvis-1.2.4/cr-1.3.2/fc-3.2.2/fh-3.1.2/datatables.min.css" />

<script type="text/javascript"
        src="https://cdn.datatables.net/v/dt/dt-1.10.13/b-1.2.4/b-colvis-1.2.4/cr-1.3.2/fc-3.2.2/fh-3.1.2/datatables.min.js"></script>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    window.Laravel = <?php echo json_encode([
        'csrfToken' => csrf_token(),
        'appPath' => route('index'),
    ]); ?>
</script>
