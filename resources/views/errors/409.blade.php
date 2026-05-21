@extends('application.base-tracker')

@section('content')
    @include('errors.partials.error-display', [
        'code'    => '409',
        'title'   => 'Member Inactive',
        'message' => 'The member you tried to view is no longer an active member of AOD.',
    ])
@endsection
