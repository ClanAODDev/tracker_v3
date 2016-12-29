@extends('layouts.app')
@section('content')


    <h2>Tracker <small>For Developers</small></h2>
    <hr />

    <div id="passport">
        <passport-clients></passport-clients>
        <passport-authorized-clients></passport-authorized-clients>
        <passport-personal-access-tokens></passport-personal-access-tokens>
    </div>

@stop