@extends('layouts.app')

@section('content')

    <div class="jumbotron welcome">
        <h1>Hello, <strong>{{ Auth::user()->name }}</strong>!</h1>
        <p>Welcome to the AOD Division Tracker, a tool for managing the members within your division in conjunction with
            the Angels of Death gaming community.</p>
    </div>

    @include('layouts.partials.all_divisions')

@endsection
