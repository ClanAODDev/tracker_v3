@extends('application.base-tracker')

@section('content')
    @include('errors.partials.error-display', [
        'code'      => '500',
        'title'     => 'System Failure',
        'message'   => 'Something went sideways on our end. Our engineers have been alerted — report this to a clan administrator if it persists.',
        'exception' => $exception ?? null,
    ])
@endsection
