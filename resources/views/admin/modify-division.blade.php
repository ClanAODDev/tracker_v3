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
        @include ('admin.forms.modify-division-form')
    </div>

@stop