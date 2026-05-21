@extends('application.base-tracker')

@section('content')
    @include('errors.partials.error-display', [
        'code'    => '404',
        'title'   => 'Signal Lost',
        'message' => 'The coordinates you entered don\'t match any known location. Check your navigation and try again — or fall back to base.',
    ])
@endsection
