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

        @include ('member.partials.notices')

        <div class="row m-b-xl">
            <div class="col-md-12">
                @include ('member.partials.general-information')
            </div>
        </div>

        @include ('member.partials.handles')
        @include ('member.partials.part-time-divisions')

        <div class="row m-t-xl">
            <div class="col-md-12">
                @can('create', \App\Note::class)
                    @include ('member.partials.notes')
                @endcan
            </div>
        </div>

@stop
