
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="UTF-8">
@php
    $theme = auth()->check() ? (auth()->user()->settings['theme'] ?? 'traditional') : 'traditional';
    $faviconPath = $theme === 'shattrath' ? 'images/logo-shattrath.svg' : 'images/logo_v2.svg';
@endphp
<link id="favicon" href="{{ asset($faviconPath) }}" type="image/svg+xml" rel="icon"/>

<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://use.fontawesome.com" crossorigin>

<!-- vendor styles -->
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css"/>
@vite(['resources/assets/scss/main.scss'])

@php
    $cdnPackages = [
        'core' => [
            'jquery@3.7.1/dist/jquery.min.js',
            'jquery-ui-dist@1.13.2/jquery-ui.min.js',
            'bootstrap@3.3.7/dist/js/bootstrap.min.js',
            'select2@4.0.13/dist/js/select2.min.js',
            'jquery-sparkline@2.4.0/jquery.sparkline.min.js',
            'toastr@2.1.4/build/toastr.min.js',
        ],
        'datatables' => [
            'datatables.net@1.13.6/js/jquery.dataTables.min.js',
            'datatables.net-bs@1.13.6/js/dataTables.bootstrap.min.js',
            'datatables.net-buttons@2.4.1/js/dataTables.buttons.min.js',
            'datatables.net-buttons-bs@2.4.1/js/buttons.bootstrap.min.js',
            'datatables.net-responsive@2.5.0/js/dataTables.responsive.min.js',
            'datatables.net-select@1.7.0/js/dataTables.select.min.js',
        ],
    ];

    $buildCdnUrl = fn($packages) => 'https://cdn.jsdelivr.net/combine/' . collect($packages)->map(fn($p) => 'npm/' . $p)->implode(',');
@endphp

<script src="{{ $buildCdnUrl($cdnPackages['core']) }}"></script>
<script src="{{ $buildCdnUrl($cdnPackages['datatables']) }}"></script>

@vite(['resources/assets/js/libs-bundle.js'])

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    window.Laravel = <?php echo json_encode([
        'csrfToken' => csrf_token(),
        'appPath' => route('index'),
        'canWorkTickets' => auth()->check() && \App\Models\TicketType::get()->contains(fn ($type) => $type->userCanWork(auth()->user())),
        'canUseBulkMode' => auth()->check() && auth()->user()->isRole(['officer', 'sr_ldr']),
        'userId' => auth()->id(),
    ]); ?>
</script>
