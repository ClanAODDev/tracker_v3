<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="UTF-8">
<script src="https://use.fontawesome.com/b59371f038.js"></script>

<link href="{{ asset('favicon.ico') }}" type="image/x-icon" rel="Shortcut Icon"/>

<link href="{{ asset('/css/libs/bootstrap.min.css') }}" rel="stylesheet">

<link href="//cdn.datatables.net/responsive/1.0.3/css/dataTables.responsive.css" rel="stylesheet">
<link rel="stylesheet" type="text/css"
href="//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.10/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css"
href="//cdnjs.cloudflare.com/ajax/libs/jquery-powertip/1.2.0/css/jquery.powertip.css">
<link href="//fonts.googleapis.com/css?family=Source+Sans+Pro" rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css"
href="//cdnjs.cloudflare.com/ajax/libs/datatables-tabletools/2.1.5/css/TableTools.css">

{!! Charts::assets() !!}


<link href="{{ asset('/css/libs/sweetalert2.css') }}" rel="stylesheet">
<link href="{{ asset('/css/app.css') }}" rel="stylesheet">


<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    window.Laravel = <?php echo json_encode([
        'csrfToken' => csrf_token(),
    ]); ?>
</script>
