@php use App\Enums\Position; @endphp

@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($division)
                <img src="{{ getDivisionIconPath($division->abbreviation) }}"
                     class="division-icon-large"/>
            @else
                <img src="{{ asset('images/logo_v2.svg') }}" width="50px" style="opacity: .2;"/>
            @endif
        @endslot
        @slot ('heading')
            {!! $member->present()->rankName !!}
            @include('member.partials.member-actions-button', ['member' => $member])
        @endslot
        @slot ('subheading')
            @if ($member->isPending)
                <span class="text-accent"><i class="fa fa-hourglass"></i> Pending member</span>
            @elseif ($member->division_id == 0)
                <span class="text-muted"><i class="fa fa-user-times"></i> Ex-AOD</span>
            @else
                {{ $member->position->name() ?? "No Position" }}
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
        @include ('member.partials.member-history')

        @can('create', \App\Models\Note::class)
            @include ('member.partials.notes')
        @endcan

        @can('recommend', $member)
            @include('member.partials.recommend')
        @endcan

    </div>

@endsection
