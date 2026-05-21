@extends('application.base-tracker')

@section('content')
    @include('errors.partials.error-display', [
        'code'     => '503',
        'title'    => 'Offline',
        'message'  => 'The Tracker is currently unavailable due to maintenance or an update in progress. Stand by — we\'ll be back online shortly.',
        'showHome' => false,
    ])
@endsection
