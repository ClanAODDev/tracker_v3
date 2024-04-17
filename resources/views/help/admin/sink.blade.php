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
            Administrative Documentation
        @endslot
    @endcomponent

    <div class="container-fluid">
        @include('help.admin.partials.sink')
    </div>
@endsection
