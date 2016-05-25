@extends('layouts.app')

@section('content')

    <div class="jumbotron welcome">
        <h1>Hi, <strong>{{ Auth::user()->name }}</strong>!</h1>
        <p>Welcome to the AOD Division Tracker, a tool for managing the members within your division in conjunction with
            the Angels of Death gaming community.</p>
        <p>v3 brings with it a complete application rewrite, and a slew of new and improved features in addition to what
            you're already used to. Visit the about page to learn more about the update.</p>
        <button class="btn btn-primary btn-large">Find Out More &raquo;</button>
    </div>

    @include('layouts.partials.all_divisions')

@endsection
