@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            {{ $division->name }}
        @endslot
        @slot ('subheading')
            Division Members
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('members', $division) !!}

        @include('division.partials.select-panel')

        <div class="row">
            <div class="col-lg-10 col-md-9">
                <div class="panel panel-filled m-b-xl" id="{{ $division->abbreviation }}">
                    <div class="panel-heading">
                        {{ $division->name }} Division
                    </div>
                    <div class='panel-body border-bottom'>
                        <div id='playerFilter'></div>
                        @include('member.partials.tag-filter')
                        @include ('member.partials.members-table-toggle')
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover members-table">
                            <thead>
                            @include ('member.partials.member-header-row')
                            </thead>
                            <tbody>
                            @foreach ($members as $member)
                                @include ('member.partials.member-data-row')
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="panel-footer">
                        <small class="slight"><span class="text-accent"><i class="fa fa-asterisk"></i></span> - On Leave
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-3">
                @include('member.partials.unit-stats', ['unitStats' => $unitStats])
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/platoon.js'])
@endsection