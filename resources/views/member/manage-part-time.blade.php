@extends('application.base')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($division)
                <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-medium"  />
            @else
                <img src="{{ asset('images/logo_v2.svg') }}" class="division-icon-medium" style="opacity: .2;" />
            @endif
        @endslot
        @slot ('heading')
            {!! $member->present()->rankName !!}
        @endslot
        @slot ('subheading')
            {{ $member->position->name ?? "No Position" }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        @include ('application.partials.back-breadcrumb', ['page' => 'profile', 'link' => route('member', $member->getUrlParams())])

        <div class="row">
            <div class="col-md-12">
                @include('member.partials.edit-part-time')
            </div>
        </div>
    </div>

@stop