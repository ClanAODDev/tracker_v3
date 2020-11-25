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
                To assist with helping clan leaders keep up with changes, a historical record of interface and process
                changes will be provided here. Minor refactoring, optimization, and other similar changes will not be
                recorded.
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>24 November 2020 - 3.31</h4>
                <hr/>
                <ul>
                    <li>Happy Holidays, AOD!</li>
                    <li>A bug was fixed pertaining to notes created from leaves of absence request. This bug caused the
                        requester not to be properly recorded in the note. Notes for existing leave requests have been
                        fixed, but there are likely old notes out there that do not have authors.
                    </li>
                    <li>User settings page added, allowing users to
                        <ul>
                            <li>Disable notifications from created tickets</li>
                            <li>Turn off the snow (but who would do that?)</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>16 November 2020 - 3.3</h4>
                <hr/>
                <ul>
                    <li>The underlying framework was upgraded to 8.14</li>
                    <li>Admin ticketing system has been implemented
                        <ul>
                            <li>Users can create and view their own tickets</li>
                            <li>Users will receive discord notifications when their ticket is updated, resolved, or
                                rejected
                            </li>
                            <li>Links included in the comments portion will be appropriately converted</li>
                            <li>Admins can now assign other admins to tickets, which will generate appropriate
                                notifications
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>28 September 2020 - 3.2</h4>
                <hr/>
                <ul>
                    <li>The underlying framework was upgraded to 8.6</li>
                    <li>Recruiters can no longer recruit themselves</li>
                    <li>Discord notifications for members removed now includes a profile link</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>12 July 2020 - 3.11</h4>
                <hr/>
                <ul>
                    <li>The underlying framework was upgraded to 7.19.1 - additional changes coming soon</li>
                    <li>Member requests can now be placed on hold by admins. Divisions will receive a discord notice
                        when this occurs.
                    </li>
                    <li>Admins can now see all member requests, even those in the 2 hour grace period.</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>1 July 2020 - 3.10.3</h4>
                <hr/>
                <ul>
                    <li>The underlying framework was upgraded to 6.x. An additional upgrade to 7.x is forthcoming.</li>
                    <li>The recruiting process has received some updates, specifically:
                        <ul>
                            <li>Forum names and member ids are much more stringently validated. Only letters, numbers,
                                dashes, spaces, and periods are acceptable
                            </li>
                            <li>Fixed a bug where users could enter thread ids instead of member ids, which was not
                                correctly validated
                            </li>
                            <li>Error messages now appear in a group at the bottom of the form</li>
                            <li>The validation process is announced rather than running silently</li>
                            <li>Prevented a race condition that would allow users to proceed while validation was
                                ongoing
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>10 May 2020 - 3.10.2</h4>
                <hr/>
                <ul>
                    <li>CPLs now appear in the SGT structure, and the SGT structure has been renamed the "Leadership
                        Structure"
                    </li>
                    <li>Recruiting process now checks that the new member has completed email verification. Recruiters
                        will see an error and cannot continue until this is fixed.
                    </li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>5 April 2020 - 3.10.1</h4>
                <hr/>
                <ul>
                    <li>Added clan leadership to Sergeants structure report</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>28 March 2020 - 3.10.0</h4>
                <hr/>
                <ul>
                    <li>Framework updated from 5.6 to 5.8 with the eventual plan to move to 6.x</li>
                    <li>Notification, logging for division transfers
                        <ul>
                            <li>Only transfers INTO a division can be tracked</li>
                        </ul>
                    </li>
                    <li>Account access is now automated, making the AOD forums the source of truth. Access is determined
                        by a member's user groups. <strong>Note:</strong> this change brings account access more in line
                        with forum privileges. As such, the <code>jr_ldr</code> role will be removed, and some
                        privileges will either be elevated to <code>Sr Ldr</code> roles, or downgraded to allow <code>Officer</code>
                        role.
                        <ul>
                            <li>Only SGTS can update members (position, rank), currently done via forum modcp</li>
                            <li>Only SGTs can update platoons, squads</li>
                            <li>Only SGTs can create platoons, squads</li>
                            <li>Only SGTs can modify the division structure</li>
                            <li>Only SGTs can manage unassigned members</li>
                            <li>Officers can now view the division structure</li>
                            <li>Officers can now manage part time</li>
                            <li>Officers can now manage ingame handles</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>17 Feb 2019 - 3.9.1</h4>
                <hr/>
                <ul>
                    <li><strong>Discord tags</strong> are now provided on user profile pages. Non-UTF-8 characters <em>should</em>
                        display correctly. Long names will break and wrap to the next line.
                    </li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>9 Feb 2019 - 3.9.0</h4>
                <hr/>
                <ul>
                    <li>Header display bug fixed (where certain items were uppercase)</li>
                    <li>Added ability to filter by toggle-able columns, like member handle. Affects division members,
                        platoon, squad views
                    </li>
                    <li>Starting work on ticketing system</li>
                    <li>Discord notification updates for continuity</li>
                    <li>Misc bug fixes, fatal error issues, visual weirdness</li>
                    <li>Admin reports for account access</li>
                    <li>Leave of absence information added to member profile</li>
                    <li>Consolidate policies governing division structure management</li>
                    <li>Dropped recruitment task validation (encoding errors prevented users from proceeding)</li>
                    <li>Support squad logos</li>
                    <li>Provision docker environment for application, dev</li>
                    <li>Ensure only senior leaders can view notes marked Sr Ldr, ensure members cannot view their own
                        notes
                    </li>
                    <li>Support discord tag in member sync</li>

                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>6 Jan 2019 - 3.8.0</h4>
                <hr/>
                <ul>
                    <li>Training information for SGT+ is now maintained by the tracker. This information can be viewed
                        either clan-wide via the Leadership Structure, or by visiting a member's profile.
                        Leadership information will only display for current Sergeants+
                    </li>
                    <ul>
                        <li>Speak to a MSGT+ for updates to a member's `last trained` date.</li>
                        <li>CO and XO appointment dates are updated whenever a member is assigned to that position via
                            the tracker.
                        </li>
                    </ul>
                    <li>Member status requests will now have a <code>2 hour</code> window before being approved by
                        leadership. This window will allow divisions to make changes to requests (specifically member
                        names) before being processed.
                    </li>
                    <li>Hotfix addressing leaves of absence whose associated notes were not being assigned to the
                        correct member id.
                    </li>
                    <li>Allow divisions to change their discord channel name in the event it does not match the typical
                        convention.
                    </li>
                    <li>Fixed bug preventing notes from being deleted by authorized users.</li>
                    <li>Added division turnover report for clan leadership.</li>
                    <li>Fixed breaking change affecting promotion reports for divisions.</li>
                    <li>Added the ability to filter retention data to specific periods of time.</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>9 Sept 2018 - 3.7.0</h4>
                <hr/>
                <ul>
                    <li>Drop tag support from the tracker</li>
                    <ul>
                        <li>Existing tags have been appended to note bodies to preserve context</li>
                    </ul>
                    <li>Add "AOD Forums" link to navigation bar</li>
                    <li>Allow line breaks in notes</li>
                    <li>Update Nova to 1.1.3</li>
                    <li>Users cannot edit their own accounts</li>
                    <li>Add assigned division Staff Sergeants (staffSergeants) to division structure data</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>9 Sept 2018 - 3.6.0</h4>
                <hr/>
                <ul>
                    <li>Upgrade framework to 5.6</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>1 Sept 2018 - 3.5.0</h4>
                <hr/>
                <ul>
                    <li>Implement member status requests</li>
                    <ul>
                        <li>Administrators will now process member status requests through the tracker. A member's
                            status will reflect whether or not status has been approved by an administrator. Status can
                            be viewed or managed via Division tools > Member Requests.
                        </li>
                        <li>Bugfix: addressed issue with member sync that could reset a member's assignment information
                            while waiting to be approved.
                        </li>
                    </ul>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>18 Aug 2018 - 3.4.0</h4>
                <hr/>
                <ul>
                    <li>Update application framework to 5.5</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>12 Aug 2018 - 3.4.0</h4>
                <hr/>
                <ul>
                    <li>Implement member status requests</li>
                    <ul>
                        <li>Administrators will now process member status requests through the tracker. A member's
                            status will reflect whether or not status has been approved by an administrator.
                        </li>
                    </ul>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>12 Aug 2018 - 3.4.0</h4>
                <hr/>
                <ul>
                    <li>Update application framework to 5.5</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>02 Aug 2018 - 3.3.1</h4>
                <hr/>
                <ul>
                    <li>Added ability to export platoons to CSV format (see footer of platoon page)</li>
                    <li>Minor bugfix to prevent empty divisions from breaking reports</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>16 Feb 2018 - 3.3.0</h4>
                <hr/>
                <ul>
                    <li>Login mechanism now leverages AOD Forum authentication system. Users will login to the tracker
                        using their forum username and password.
                    </li>
                    <ul>
                        <li>
                            Tracker and forum accounts will continue to be separate. However, when authenticating as a
                            new user to the Tracker, an account will automatically be generated. Management of account
                            access will continue to be managed through the tracker.
                        </li>
                        <li>
                            <em>In a later update</em>, account access will be granted based on the forum member groups
                            a user belongs to.
                        </li>
                        <li>Password resets and account registration will be handled directly through the AOD forums.
                            Just as before, users that do not already have a forum account will not be able to log into
                            the tracker.
                        </li>
                        <li>Users exceeding 5 failed login attempts will be locked out for 60 seconds.</li>
                        <li>Added initial request date for leaves of absence listing</li>
                    </ul>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>20 Jan 2018 - 3.2.9</h4>
                <hr/>
                <ul>
                    <li>Added division outstanding inactive report, migrated ts report and census report to own
                        navigation dropdown
                    </li>
                    <li>Added ability to reorder division recruiting tasks</li>
                    <li class="text-warning">Disabled tag management across application. Tags will likely be
                        incorporated into note bodies, and the ability / requirement to set a specific tag will be
                        removed completely.
                    </li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>28 Dec 2017 - 3.2.7</h4>
                <hr/>
                <ul>
                    <li>Added AOD status to 'Manage Part Time' report, allowing leadership to see if an assigned
                        part-timer has been removed from AOD, and prune them appropriately.
                    </li>
                    <ul>
                        <li>
                            <strong>Note:</strong> In order to ensure divisions have the chance to clean up part-timers
                            from internal systems (ingame organizations), part-time membership will no longer be
                            automatically reset when a member is removed from AOD.
                        </li>
                    </ul>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>30 Oct 2017 - 3.2.6</h4>
                <hr/>
                <ul>
                    <li>Added division ingame report functionality (boilerplate, not all divisions will have supported
                        ingame report functionality)
                    </li>
                    <li>Added division retention information. Initially, only the current month's recruitment data will
                        be available. Soon, by-month query and removals will be available.
                    </li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>09 Oct 2017 - 3.2.5</h4>
                <hr/>
                <ul>
                    <li>Added ability to explore division notes, and filter by tag</li>
                    <li>Tags and notes are now available when removing an individual member via their profile</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>17 Sept 2017 - 3.2.4</h4>
                <hr/>
                <ul>
                    <li>Added bb-code for sharing promotions on promotions reports page, also added a default select
                        option to promotions period dropdown
                    </li>
                    <li>Fixed erroneous inactive (flagged) members view</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>12 Sept 2017 - 3.2.3</h4>
                <hr/>
                <ul>
                    <li>Ingame handle and part-time management has been moved out of the "Edit Member" area, and into
                        their own areas. You can still navigate to these areas via the respective "Manage" buttons on a
                        member profile. Additionally, junior leaders can now access these areas.
                    </li>
                    <li>Corrected bug where assigning a platoon leader who was previously a squad leader would not
                        properly reset the losing squad.
                    </li>
                    <li>Ingame name field no longer auto-populates with forum name in recruitment process</li>
                    <li>"Leaving page" warning no longer appears on the final step of the recruiting process.</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>6 Sept 2017 - 3.2.2</h4>
                <hr/>
                <ul>
                    <li>Added flagged member bb-code for sharing members removed</li>
                    <li>Notes can now be added to Ex-AOD members.</li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>3 Sept 2017 - 3.2.1</h4>
                <hr/>
                <ul>
                    <li>Minor logic issues fixed with assigning platoon leaders, squad leaders</li>
                    <li>Removed deprecated member assignment functionality in Manage Platoon, Manage Squad views</li>
                    <li>Members on leave now appear "dimmed" in member lists. Leave icon is now a grey color.</li>
                    <li>Added a mass-pm button on part-timers management view</li>
                    <li>Member profile page routes now include a slug that displays rank and name to make linking to
                        member profiles more accessible.
                    </li>
                    <li>Users can now edit their own member profile, to manage position, handles, part-time status.</li>
                    <li>Squad information now listed on the inactive members section. TS activity has been added to the
                        flagged view.
                    </li>
                    <li>List division inactive by percentage instead of raw number. Number can still be viewed by
                        hovering over the sparkline graph
                    </li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>31 August 2017 - 3.2.0</h4>
                <hr/>
                <ul>
                    <li>Slack management tools (for users, channels) implemented for division commanders and clan
                        administration.
                    </li>
                    <li>Recruit information added to member profiles. Members without recruits will not have this
                        section.
                    </li>
                    <li>Added member rank and name to profile view slug for improved deeplinking</li>
                    <li>Notice added to pending member profiles regarding temporarily unavailable TS activity
                        information
                    </li>
                </ul>
            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">
                <h4>23 August 2017 - 3.1.1</h4>
                <hr/>
                <ul>
                    <li>Division census report updated to show a range of dates. Hover tooltip shows the exact day and
                        member count. Notes have been omitted until a more viable solution is implemented. Graph
                        ordering was also modified to appear left to right.
                    </li>
                </ul>

            </div>
        </div>

        <div class="row m-t-xl">
            <div class="col-md-12">

                <h4>22 August 2017 - 3.1.0</h4>
                <hr/>
                <ul>
                    <li>Added application changelog</li>
                    <li>Implemented
                        <code>Reset member assignments</code> function on the member profile page to resolve erroneous
                        platoon, squad assignments. This is for quickly resolving "disappearing" member issues, as well
                        as convenience for members transferring out of a division.
                    </li>
                    <li>Added unassigned members area to division view, deprecating the unassigned member area in the
                        platoon edit view. This will be removed in a later update.
                        <ul>
                            <li>Note: the manage members area deprecates the unassigned member in the squad edit view.
                                This will also be removed at the same time.
                            </li>
                        </ul>
                    </li>
                    <li>
                        Fix issue where leave type isn't properly selected, or updated when editing an existing leave of
                        absence.
                    </li>
                    <li>Add notice to member note when that note is associated with an existing leave of absence.</li>
                    <li>
                        Add pending member status attribute
                        <ul>
                            <li>New recruits will be flagged as pending to prevent the forum sync from erroneously
                                resetting assignment information (because they are not listed as active). Pending member
                                (and Ex-AOD) statuses will now reflect on the member profile page.
                            </li>
                        </ul>
                    </li>
                    <li>Added member name variable <code>@{{ name }}</code> to division welcome PM</li>
                </ul>
            </div>
        </div>

    </div>
@stop
