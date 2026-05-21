@extends('application.base-tracker')

@section('content')
    @include('errors.partials.error-display', [
        'code'    => '403',
        'title'   => 'Access Denied',
        'message' => 'Your clearance level is insufficient for this area. Contact your division leadership or a clan administrator if you believe this is a mistake.',
    ])
@endsection
