@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($division)
                <img src="{{ getDivisionIconPath($division->abbreviation) }}" />
            @else
                <img src="{{ asset('images/logo_v2.svg') }}" width="50px" style="opacity: .2;" />
            @endif
        @endslot
        @slot ('heading')
            {!! $member->present()->rankName !!}
            @include('member.partials.edit-member-button', ['member' => $member])
        @endslot
        @slot ('subheading')
            {{ $member->position->name  }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('member', $member) !!}

        @include ('member.partials.general-information')
        @include ('member.partials.aliases')

        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#notes-panel" aria-expanded="true"> Notes</a></li>
                <li><a data-toggle="tab" href="#division-data-panel" aria-expanded="false">Division Data</a></li>
            </ul>
            <div class="tab-content">
                <div id="notes-panel" class="tab-pane active">
                    <div class="panel-body">
                        @include('member.partials.notes')
                    </div>
                </div>
                <div id="division-data-panel" class="tab-pane">
                    <div class="panel-body">
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. A accusamus accusantium beatae, blanditiis dicta eius explicabo fugit itaque iure labore, laborum officia officiis placeat, porro possimus reiciendis repellat sed voluptas?
                    </div>
                </div>
            </div>

        </div>

        @include ('member.partials.part-time-divisions')
    </div>


@stop
