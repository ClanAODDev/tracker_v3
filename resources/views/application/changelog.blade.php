@extends('application.base')

@section('content')
    @component ('application.components.view-heading')
        @slot ('currentPage')
            v3
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px" />
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
                To assist with helping clan leaders keep up with changes, a historical record of interface and process changes will be provided here. Minor refactoring, optimization, and other similar changes will not be recorded.
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>22 August 2017 - 3.1.0</h4>
                <hr />
                <ul>
                    <li>Added application changelog</li>
                    <li>Implemented
                        <mark>Reset member assignments</mark>
                        function on the member profile page to resolve erroneous platoon, squad assignments. This is for quickly resolving "disappearing" member issues, as well as convenience for members transferring out of a division.
                    </li>
                    <li>Added unassigned members area to division view, deprecating the unassigned member area in the platoon edit view. This will be removed in a later update.
                        <ul>
                            <li>Note: the manage members area deprecates the unassigned member in the squad edit view. This will also be removed at the same time.
                            </li>
                        </ul>
                    </li>
                    <li>
                        Fix issue where leave type isn't properly selected, or updated when editing an existing leave of absence.
                    </li>
                    <li>Add notice to member note when that note is associated with an existing leave of absence.</li>
                </ul>
            </div>
        </div>

    </div>
@stop
