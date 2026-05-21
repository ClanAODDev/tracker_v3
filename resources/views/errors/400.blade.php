@extends('application.base-tracker')

@section('content')
    @include('errors.partials.error-display', [
        'code'    => '400',
        'title'   => 'Bad Request',
        'message' => 'The request couldn\'t be processed. If you typed a URL manually, check it for errors — otherwise contact a clan administrator.',
    ])
@endsection
