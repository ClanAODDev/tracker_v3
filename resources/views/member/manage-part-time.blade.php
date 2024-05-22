@extends('application.base-tracker')
@section('content')

    @component ('application.components.division-heading', ['division' => $division])
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

@endsection