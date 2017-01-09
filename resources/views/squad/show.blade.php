@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('squad', $squad->platoon->division, $squad->platoon) !!}

@stop
