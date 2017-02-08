@extends('application.base')
@section('content')

    {!! Breadcrumbs::render('squad', $squad->platoon->division, $squad->platoon) !!}

@stop
