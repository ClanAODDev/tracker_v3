@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            <span class="hidden-xs">{{ $division->name }}</span>
        @endslot
        @slot ('subheading')
            Division Members
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('members', $division) !!}

        @include('division.partials.select-panel')

        <div class="row">
            <div class="col-md-10">
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
            <div class="col-md-2">
                <div class="panel panel-filled">
                    <div class="panel-body">
                        <h1>
                            <i class="pe pe-7s-users pe-lg text-warning"></i> {{ $members->count() }}
                            <small class="slight">Members</small>
                        </h1>
                    </div>
                </div>
                <div class="panel panel-filled hidden-xs hidden-sm">
                    <div class="panel-heading">
                        Discord Activity
                    </div>
                    <div class="panel-body">
                        <canvas class="voice-activity-chart" data-labels="{{ json_encode($voiceActivityGraph['labels'])
                         }}"
                                data-values="{{ json_encode($voiceActivityGraph['values']) }}"
                                data-colors="{{ json_encode($voiceActivityGraph['colors']) }}"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    <script src="{!! asset('/js/platoon.js?v=2.21') !!}"></script>
@endsection