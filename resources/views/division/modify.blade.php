@extends('layouts.app')
@section('content')

    <h2>
        <strong>{!! $division->name !!}</strong>
        <small>Edit Division</small>

        <div class="btn-group btn-group-sm pull-right">
            <a href="{{ action('DivisionController@show', $division->abbreviation) }}"
               class="btn btn-default"><i class="fa fa-times fa-lg"></i><span class="hidden-sm hidden-xs"> Cancel</span>
            </a>

            <a href="#" class="btn btn-success">
                <i class="fa fa-check fa-lg"></i><span class="hidden-sm hidden-xs"> Save Changes</span>
            </a>
        </div>
    </h2>
    <hr/>

@stop
