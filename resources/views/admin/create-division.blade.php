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
                <h4>Create New Division</h4>
                <div>
                    <p>New divisions should be created within the AOD forums before being created on the tracker. This will ensure that the division will properly sync with the forums. The name of the division is particularly important - it
                        <em>must</em> match the real name used on the forums.</p>
                    <p>The game abbreviation is used to display the icon, and also determines the path to the division on the tracker.</p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-filled">
                    @include('application.partials.errors')
                    {!! Form::model(\App\Division::class, ['method' => 'post', 'route' => 'adminStoreDivision']) !!}
                    @include ('admin.forms.modify-division-form')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

@section('footer_scripts')
    <script src="{!! asset('/js/division.js') !!}"></script>
@stop

@stop