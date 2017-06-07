@extends('application.base')

@section('content')


@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Manage divisions and members within the AOD organization
        @endslot
    @endcomponent

    <div class="container-fluid">

        <div class="row">
            <div class="col-sm-3 hidden-xs pull-right" id="division-nav">
                @foreach($divisions as $division)
                    <a href="#{{ $division->abbreviation }}" class="list-group-item">
                        {{ $division->name }}
                    </a>
                @endforeach
            </div>

            <div class="col-sm-9">
                @foreach ($divisions as $division)
                    <div class="panel">
                        <div class="panel-heading" id="{{ $division->abbreviation }}">
                            <h4>{{ $division->name }} ({{ $division->sergeants_count }})</h4>
                        </div>
                        <div class="panel-body">

                            @foreach ( $division->sergeants as $member)

                                <a href="{{ route('member', $member->clan_id) }}"
                                   class="col-lg-5 panel panel-filled m-r">
                                    <div class="panel-body">
                                        {{ $member->present()->rankName }}
                                    </div>

                                </a>
                            @endforeach

                        </div>
                    </div>
                @endforeach

                    <div class="alert alert-danger alert-dismissible fade in" role="alert"> <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button> <h4>Oh snap! You got an error!</h4> <p>Change this and that and try again. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Cras mattis consectetur purus sit amet fermentum.</p> <p> <button type="button" class="btn btn-danger">Take this action</button> <button type="button" class="btn btn-default">Or do this</button> </p> </div>
            </div>
        </div>
    </div>
@stop