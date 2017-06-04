@extends('application.base')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($division)
                <img src="{{ getDivisionIconPath($division->abbreviation) }}"
                     class="division-icon-large" />
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

        {!! Breadcrumbs::render('member', $member, $division) !!}

        <div class="row">
            <div class="col-md-12">
                @include ('member.partials.general-information')
            </div>
        </div>

        <div class="row m-b-lg m-t-n">
            @can('create', \App\Note::class)
                <div class="col-md-12">
                    @include ('member.partials.notes')
                </div>
            @else
                <div class="col-md-12">
                    @include ('member.partials.notes-hidden')
                </div>
            @endcan
        </div>

        @include ('member.partials.handles')
        @include ('member.partials.part-time-divisions')
    </div>

@stop
