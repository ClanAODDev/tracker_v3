@extends('application.base')

@section('metadata')
    <meta property="og:title" content="{{ $member->present()->rankName }}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="http://example.com" />
    <meta property="og:image" content="http://example.com/images/image.jpg" />
@stop

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
            @if ($member->isPending)
                <span class="text-accent"><i class="fa fa-hourglass"></i>  Pending member</span>
            @elseif ($member->division_id == 0)
                <span class="text-muted"><i class="fa fa-user-times"></i> Ex-AOD</span>
            @else
                {{ $member->position->name or "No Position" }}
            @endif
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
        @include ('member.partials.recruits')

        <div class="row m-t-xl">
            <div class="col-md-12">
                @can('create', \App\Note::class)
                    @include ('member.partials.notes')
                @endcan
            </div>
        </div>

@stop
