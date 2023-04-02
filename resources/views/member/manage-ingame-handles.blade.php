@extends('application.base-tracker')
@section('content')

    @component ('application.components.division-heading')
        @slot ('icon')
            @if ($division)
                <img src="{{ getDivisionIconPath($division->abbreviation) }}" class="division-icon-large" />
            @else
                <img src="{{ asset('images/logo_v2.svg') }}" width="50px" style="opacity: .2;" />
            @endif
        @endslot
        @slot ('heading')
            {!! $member->present()->rankName !!}
        @endslot
        @slot ('subheading')
            {{ $member->position->name() ?? "No Position" }}
        @endslot
    @endcomponent

    <div class="container-fluid">
        @include ('application.partials.back-breadcrumb', ['page' => 'profile', 'link' => route('member', $member->getUrlParams())])

        <div class="row">
            <div class="col-md-12">
                <h4>Manage Ingame Handles</h4>

                <p>Here you can manage all of a member's ingame handles. All divisions have a default primary ingame handle, so if this member belongs to you, ensure that it exists and is accurate.</p>

                <p>To add a handle, first
                    <code>show all</code> and activate the handle(s) you wish to add. Then provide values, and finally, save your changes.
                </p>

                <div id="profile-container">
                    <manage-handles :handles="{{ $handles  }}"
                                    :member-id="{{ $member->id }}"></manage-handles>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    <script src="{!! asset('/js/manage-member.js?v=4.5') !!}"></script>
@endsection
