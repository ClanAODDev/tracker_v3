@extends('application.base-tracker')

@section('content')
    @include('errors.partials.error-display', [
        'code'    => '405',
        'title'   => 'Not Permitted',
        'message' => 'You\'re not allowed to do that. Contact your division leadership or a clan administrator if you believe this is a mistake.',
    ])
@endsection
