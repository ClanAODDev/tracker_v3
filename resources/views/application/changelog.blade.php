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
                <h4>16 Feb 2018 - 3.3.0</h4>
                <hr />
                <ul>
                    <li>Login mechanism now leverages AOD Forum authentication system. Users will login to the tracker using their forum username and password.</li>
                    <ul>
                        <li>
                            Tracker and forum accounts will continue to be separate. However, when authenticating as a new user to the Tracker, an account will automatically be generated. Management of account access will continue to be managed through the tracker.
                        </li>
                        <li>
                            <em>In a later update</em>, account access will be granted based on the forum member groups a user belongs to.
                        </li>
                        <li>Password resets and account registration will be handled directly through the AOD forums. Just as before, users that do not already have a forum account will not be able to log into the tracker.</li>
                        <li>Users exceeding 5 failed login attempts will be locked out for 60 seconds.</li>
                        <li>Added initial request date for leaves of absence listing</li>
                    </ul>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>20 Jan 2018 - 3.2.9</h4>
                <hr />
                <ul>
                    <li>Added division outstanding inactive report, migrated ts report and census report to own navigation dropdown</li>
                    <li>Added ability to reorder division recruiting tasks</li>
                    <li class="text-warning">Disabled tag management across application. Tags will likely be incorporated into note bodies, and the ability / requirement to set a specific tag will be removed completely.</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>28 Dec 2017 - 3.2.7</h4>
                <hr />
                <ul>
                    <li>Added AOD status to 'Manage Part Time' report, allowing leadership to see if an assigned part-timer has been removed from AOD, and prune them appropriately.</li>
                    <ul>
                        <li>
                            <strong>Note:</strong> In order to ensure divisions have the chance to clean up part-timers from internal systems (ingame organizations), part-time membership will no longer be automatically reset when a member is removed from AOD.
                        </li>
                    </ul>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>30 Oct 2017 - 3.2.6</h4>
                <hr />
                <ul>
                    <li>Added division ingame report functionality (boilerplate, not all divisions will have supported ingame report functionality)</li>
                    <li>Added division retention information. Initially, only the current month's recruitment data will be available. Soon, by-month query and removals will be available.</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>09 Oct 2017 - 3.2.5</h4>
                <hr />
                <ul>
                    <li>Added ability to explore division notes, and filter by tag</li>
                    <li>Tags and notes are now available when removing an individual member via their profile</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>17 Sept 2017 - 3.2.4</h4>
                <hr />
                <ul>
                    <li>Added bb-code for sharing promotions on promotions reports page, also added a default select option to promotions period dropdown</li>
                    <li>Fixed erroneous inactive (flagged) members view</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>12 Sept 2017 - 3.2.3</h4>
                <hr />
                <ul>
                    <li>Ingame handle and part-time management has been moved out of the "Edit Member" area, and into their own areas. You can still navigate to these areas via the respective "Manage" buttons on a member profile. Additionally, junior leaders can now access these areas.</li>
                    <li>Corrected bug where assigning a platoon leader who was previously a squad leader would not properly reset the losing squad.</li>
                    <li>Ingame name field no longer auto-populates with forum name in recruitment process</li>
                    <li>"Leaving page" warning no longer appears on the final step of the recruiting process.</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>6 Sept 2017 - 3.2.2</h4>
                <hr />
                <ul>
                    <li>Added flagged member bb-code for sharing members removed</li>
                    <li>Notes can now be added to Ex-AOD members.</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>3 Sept 2017 - 3.2.1</h4>
                <hr />
                <ul>
                    <li>Minor logic issues fixed with assigning platoon leaders, squad leaders</li>
                    <li>Removed deprecated member assignment functionality in Manage Platoon, Manage Squad views</li>
                    <li>Members on leave now appear "dimmed" in member lists. Leave icon is now a grey color.</li>
                    <li>Added a mass-pm button on part-timers management view</li>
                    <li>Member profile page routes now include a slug that displays rank and name to make linking to member profiles more accessible.</li>
                    <li>Users can now edit their own member profile, to manage position, handles, part-time status.</li>
                    <li>Squad information now listed on the inactive members section. TS activity has been added to the flagged view.</li>
                    <li>List division inactive by percentage instead of raw number. Number can still be viewed by hovering over the sparkline graph</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>31 August 2017 - 3.2.0</h4>
                <hr />
                <ul>
                    <li>Slack management tools (for users, channels) implemented for division commanders and clan administration.</li>
                    <li>Recruit information added to member profiles. Members without recruits will not have this section.</li>
                    <li>Added member rank and name to profile view slug for improved deeplinking</li>
                    <li>Notice added to pending member profiles regarding temporarily unavailable TS activity information</li>
                </ul>
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
