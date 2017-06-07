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
                <h4>Create New Handle</h4>
                <div>
                    <p>Handles are used to track ingame member aliases. Before creating a new handle, ensure that the handle type you wish to create does not already exist. Additionally, use the comments field to specify the unique identifier for your type. Ideally, this is either alphanumerical or numerical.</p>
                    <p>Handles <span class="text-accent">should not</span> be entire URLs.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-filled">
                    @include('application.partials.errors')
                    {!! Form::model(\App\Handle::class, ['method' => 'post', 'route' => 'adminStoreHandle']) !!}
                    @include ('admin.forms.modify-handle-form')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

@section('footer_scripts')
    <script src="{!! asset('/js/division.js') !!}"></script>
@stop

@stop