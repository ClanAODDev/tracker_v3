@extends('application.base')

@section('content')


    @component ('application.components.view-heading')
        @slot ('currentPage')
            Admin CP
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Administration CP
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <h4>Update Handle</h4>
                <div>
                    <p>Handles are used to track ingame member aliases. Before creating a new handle, ensure that the handle type you wish to create does not already exist. Additionally, use the comments field to provide an example. Ex. <code>JohnDoe#97363</code></p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-filled">
                    @include('application.partials.errors')
                    {!! Form::model($handle, ['method' => 'patch', 'route' => ['adminUpdateHandle', $handle]]) !!}
                    @include ('admin.forms.modify-handle-form')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <hr />
        {!! Form::model($handle, ['method' => 'delete', 'route' => ['adminDeleteHandle', $handle]]) !!}
        @include('admin.forms.delete-handle-form')
        {!! Form::close() !!}

    </div>

@stop