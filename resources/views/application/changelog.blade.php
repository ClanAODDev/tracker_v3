@extends('application.base-tracker')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px"/>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Changelog notes
        @endslot
    @endcomponent

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <p>To assist with helping clan leaders keep up with changes, a historical record of interface and
                    process changes will be provided here. Minor refactoring, optimization, and other similar changes
                    will not be recorded. Refer to the <a href="https://github.com/ClanAODDev/tracker_v3/commits/main/">commit
                        history on GitHub</a> for a full changelog.</p>
            </div>
        </div>

        @include('application.partials.changes')

    </div>
@endsection
