@extends('layouts.app')
@section('content')

    {!! Breadcrumbs::render('divisions', $member->primaryDivision() ) !!}


    <h1> {!! Helpers::avatar($member->email, 'small')  !!} {{ $member->rank->abbreviation }} {{ $member->name }}</h1>
    <hr/>

@stop