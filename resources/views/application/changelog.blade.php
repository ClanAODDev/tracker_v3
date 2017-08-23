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
                <h4>23 August 2017 - 3.1.1</h4>
                <hr />
                <ul>
                    <li>Division census report updated to show a range of dates. Hover tooltip shows the exact day and member count. Notes have been omitted until a more viable solution is implemented. Graph ordering was also modified to appear left to right.</li>
                </ul>

            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">

                <h4>22 August 2017 - 3.1.0</h4>
                <hr />
                <ul>
                    <li>Added application changelog</li>
                    <li>Implemented
                        <code>Reset member assignments</code> function on the member profile page to resolve erroneous platoon, squad assignments. This is for quickly resolving "disappearing" member issues, as well as convenience for members transferring out of a division.
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
                    <li>
                        Add pending member status attribute
                        <ul>
                            <li>New recruits will be flagged as pending to prevent the forum sync from erroneously resetting assignment information (because they are not listed as active). Pending member (and Ex-AOD) statuses will now reflect on the member profile page.</li>
                        </ul>
                    </li>
                    <li>Added member name variable <code>@{{ name }}</code> to division welcome PM</li>
                </ul>
            </div>
        </div>

    </div>
@stop
