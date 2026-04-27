@extends('application.base-tracker')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Documentation
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-help2"></i>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Recruiting New Members
        @endslot
    @endcomponent

    <div class="container-fluid markdown-content">
        @include('help.md-partials.recruiting')
    </div>
@endsection
