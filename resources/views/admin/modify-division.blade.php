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
                <h4>Update {{ $division->name }}</h4>
                <div>
                    <p>Division name should match the real name of the division on the AOD forums.</p>
                    <p>Marking a division inactive will:</p>
                    <ul>
                        <li>Stop data syncing from the AOD forums for that division</li>
                        <li>Remove the division from the tracker listing</li>
                        <li>Prevent non-admin users from accessing the division</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6">
                <div class="panel panel-filled">
                    @include('application.partials.errors')
                    {!! Form::model($division, ['method' => 'patch', 'route' => ['adminUpdateDivision', $division->abbreviation]]) !!}
                    @include ('admin.forms.modify-division-form')
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        @can('delete', $division)
            <hr />
            {!! Form::model($division, ['method' => 'delete', 'route' => ['adminDeleteDivision', $division->abbreviation]]) !!}
            @include('admin.forms.delete-division-form')
            {!! Form::close() !!}
        @endcan
    </div>

@stop