@extends('application.base-tracker')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Recruiting
        @endslot
        @slot ('icon')
            <i class="pe page-header-icon pe-7s-users"></i>
        @endslot
        @slot ('heading')
            <span class="hidden-xs">Add New Recruit</span>
            <span class="visible-xs">Recruiting</span>
        @endslot
        @slot ('subheading')
            Add a new member to AOD
        @endslot
    @endcomponent

    <div class="container-fluid">
        <h3>Select a division</h3>
        <p>To start the recruiting process, select the division you wish to add a new recruit for. Keep in mind that each division's recruiting process is unique to that division, so ensure you communicate with them before processing a recruit for a division that is not your own.</p>

        <hr />

        <div class="row">
            @foreach ($divisions as $division)
                <div class="col-sm-4">
                    <a href="{{ route('recruiting.form', [$division->slug]) }}" class="panel panel-filled">
                        <div class="panel-body">
                            <h4>
                                <img src="{{ $division->getLogoPath() }}"
                                     class="division-icon-medium" />
                                {{ $division->name }}
                            </h4>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

    </div>
@endsection