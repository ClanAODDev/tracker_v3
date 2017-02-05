@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        

        <div class="row my-division">
            @include('layouts.partials.my-division')
        </div>

        <div class="row divisions">
            @include('layouts.partials.divisions')
        </div>

    </div>

@stop
