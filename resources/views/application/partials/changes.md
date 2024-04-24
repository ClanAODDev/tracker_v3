#### 30 March 2024 - 3.7.1
* Discord activity now being tracked
    * Forum data sync will include discord status and last activity
    * Possible states include:
        * **Connected** - member is here right now
        * **Disconnected** - member was here but isn't anymore
        * **Never connected** - member never connected to our discord
        * **Never configured** - no discord account information was shared with AOD (via member profile)
    * A status icon can be found on member profiles
    * Divisions have access to a "voice comms" report identifying members with issues requiring attention

#### 31 December - 1 January 2022 - 3.7
* Rank changes and division transfers/assignments are now recorded for posterity. Historical changes will be listed on
  member profiles. NCOs will soon have the ability to retroactively create both. MSGT+ have the ability to create
  records administratively.
    * The recruiting history section has been consolidated into a tabbed pane, along with rank and division history.
* Significant improvements made to the forum sync process, enabling more accurate change detection.
* Confetti - added in lieu of snow for a short time

#### 5 September 2021 - 3.6
* Division leaders now have the [ability to manage AOD member status](https://www.clanaod.net/forums/showthread.php?t=251106&goto=newpost).
* Slightly improved appearance of overdue requests
* Members who opt-out of receiving forum PMs are now filtered out of mass PMs created through the tracker

#### 5 September 2021 - 3.51
* Additional information added to member listing pages (platoon, squad, all members)
* Bug fixed causing user roles to not properly sync when new members first log into the tracker
* Bug fixed affecting creating of new squads
* Notes added to division structure twig templates for handling empty objects

#### 18 July 2021 - 3.5
* Mass PM functionality for division members, platoon, and squad views is now replaced with a more functional PM process
  allowing explicit rows to be selected. To use it:
    * Select a row and hold either `CTRL` or `SHIFT`
    * Select additional rows depending on the desired range
    * Note: at least 2 members must be selected before sending a PM

#### 10 June 2021 - 3.4
* Dropping support for staff sergeant assignments within divisions

#### 4 January 2021 - 3.33
* Squads can now be exported to CSV
* Fixed last promoted date on squad and platoon views. They now show "never" if a date is not available.

#### 9 December 2020 - 3.32
* Recruiting process now auto-populates with the forum name of the member id being validated. This will help ensure the
  recruiter is targeting the correct member id
* Platoon "manage member" control now includes direct recruit information in squads
* Division listings now properly sort alphabetically by name (sorry iRacing)

#### 24 November 2020 - 3.31
* Happy Holidays, AOD!
* A bug was fixed pertaining to notes created from leaves of absence request. This bug caused the requester not to be
  properly recorded in the note. Notes for existing leave requests have been fixed, but there are likely old notes out
  there that do not have authors.
* User settings page added, allowing users to
    * Disable notifications from created tickets
    * Turn off the snow (but who would do that?)

#### 16 November 2020 - 3.3
* The underlying framework was upgraded to 8.14
* Admin ticketing system has been implemented
    * Users can create and view their own tickets
    * Users will receive discord notifications when their ticket is updated, resolved, or rejected
    * Links included in the comments portion will be appropriately converted
    * Admins can now assign other admins to tickets, which will generate appropriate notifications

#### 28 September 2020 - 3.2
* The underlying framework was upgraded to 8.6
* Recruiters can no longer recruit themselves
* Discord notifications for members removed now includes a profile link

#### 12 July 2020 - 3.11
* The underlying framework was upgraded to 7.19.1 - additional changes coming soon
* Member requests can now be placed on hold by admins. Divisions will receive a discord notice when this occurs.
* Admins can now see all member requests, even those in the 2 hour grace period.

#### 1 July 2020 - 3.10.3
* The underlying framework was upgraded to 6.x. An additional upgrade to 7.x is forthcoming.
* The recruiting process has received some updates, specifically:
    * Forum names and member ids are much more stringently validated. Only letters, numbers, dashes, spaces, and periods
      are acceptable
    * Fixed a bug where users could enter thread ids instead of member ids, which was not correctly validated
    * Error messages now appear in a group at the bottom of the form
    * The validation process is announced rather than running silently
    * Prevented a race condition that would allow users to proceed while validation was ongoing

#### 10 May 2020 - 3.10.2
* CPLs now appear in the SGT structure, and the SGT structure has been renamed the "Leadership Structure"
* Recruiting process now checks that the new member has completed email verification. Recruiters will see an error and
  cannot continue until this is fixed.

#### 5 April 2020 - 3.10.1
* Added clan leadership to Sergeants structure report

#### 28 March 2020 - 3.10.0
* Framework updated from 5.6 to 5.8 with the eventual plan to move to 6.x
* Notification, logging for division transfers
    * Only transfers INTO a division can be tracked*   Account access is now automated, making the AOD forums the source
      of truth. Access is determined by a member's user groups. **Note:** this change brings account access more in line
      with forum privileges. As such, the `jr_ldr` role will be removed, and some privileges will either be elevated
      to `Sr Ldr` roles, or downgraded to allow `Officer` role.
    * Only SGTS can update members (position, rank), currently done via forum modcp
    * Only SGTs can update platoons, squads
    * Only SGTs can create platoons, squads
    * Only SGTs can modify the division structure
    * Only SGTs can manage unassigned members
    * Officers can now view the division structure
    * Officers can now manage part time
    * Officers can now manage ingame handles

#### 17 Feb 2019 - 3.9.1
* Discord tags are now provided on user profile pages. Non-UTF-8 characters _should_ display correctly. Long names
  will break and wrap to the next line.

#### 9 Feb 2019 - 3.9.0
* Header display bug fixed (where certain items were uppercase)
* Added ability to filter by toggle-able columns, like member handle. Affects division members, platoon, squad views
* Starting work on ticketing system
* Discord notification updates for continuity
* Misc bug fixes, fatal error issues, visual weirdness
* Admin reports for account access
* Leave of absence information added to member profile
* Consolidate policies governing division structure management
* Dropped recruitment task validation (encoding errors prevented users from proceeding)
* Support squad logos
* Provision docker environment for application, dev
* Ensure only senior leaders can view notes marked Sr Ldr, ensure members cannot view their own notes
* Support discord tag in member sync

#### 6 Jan 2019 - 3.8.0
* Training information for SGT+ is now maintained by the tracker. This information can be viewed either clan-wide via
  the Leadership Structure, or by visiting a member's profile. Leadership information will only display for current
  Sergeants+
* Speak to a MSGT+ for updates to a member's \`last trained\` date.
* CO and XO appointment dates are updated whenever a member is assigned to that position via the tracker.
* Member status requests will now have a `2 hour` window before being approved by leadership. This window will allow
  divisions to make changes to requests (specifically member names) before being processed.
* Hotfix addressing leaves of absence whose associated notes were not being assigned to the correct member id.
* Allow divisions to change their discord channel name in the event it does not match the typical convention.
* Fixed bug preventing notes from being deleted by authorized users.
* Added division turnover report for clan leadership.
* Fixed breaking change affecting promotion reports for divisions.
* Added the ability to filter retention data to specific periods of time.

#### 9 Sept 2018 - 3.7.0
* Drop tag support from the tracker
* Existing tags have been appended to note bodies to preserve context
* Add "AOD Forums" link to navigation bar
* Allow line breaks in notes
* Update Nova to 1.1.3
* Users cannot edit their own accounts
* Add assigned division Staff Sergeants (staffSergeants) to division structure data

#### 9 Sept 2018 - 3.6.0
* Upgrade framework to 5.6

#### 1 Sept 2018 - 3.5.0
* Bugfix: addressed issue with member sync that could reset a member's assignment information while waiting to be
  approved.

#### 18 Aug 2018 - 3.4.0
* Update application framework to 5.5

#### 12 Aug 2018 - 3.4.0
* Implement member status requests
* Administrators will now process member status requests through the tracker. A member's status will reflect whether or
  not status has been approved by an administrator.

#### 02 Aug 2018 - 3.3.1
* Added ability to export platoons to CSV format (see footer of platoon page)
* Minor bugfix to prevent empty divisions from breaking reports

#### 16 Feb 2018 - 3.3.0
* Login mechanism now leverages AOD Forum authentication system. Users will login to the tracker using their forum
  username and password.
* Tracker and forum accounts will continue to be separate. However, when authenticating as a new user to the Tracker, an
  account will automatically be generated. Management of account access will continue to be managed through the tracker.
* _In a later update_, account access will be granted based on the forum member groups a user belongs to.
* Password resets and account registration will be handled directly through the AOD forums. Just as before, users that
  do not already have a forum account will not be able to log into the tracker.
* Users exceeding 5 failed login attempts will be locked out for 60 seconds.
* Added initial request date for leaves of absence listing

#### 20 Jan 2018 - 3.2.9
* Added division outstanding inactive report, migrated ts report and census report to own navigation dropdown
* Added ability to reorder division recruiting tasks
* Disabled tag management across application. Tags will likely be incorporated into note bodies, and the ability /
  requirement to set a specific tag will be removed completely.

#### 28 Dec 2017 - 3.2.7
* Added AOD status to 'Manage Part Time' report, allowing leadership to see if an assigned part-timer has been removed
  from AOD, and prune them appropriately.
* **Note:** In order to ensure divisions have the chance to clean up part-timers from internal systems (ingame
  organizations), part-time membership will no longer be automatically reset when a member is removed from AOD.

#### 30 Oct 2017 - 3.2.6
* Added division ingame report functionality (boilerplate, not all divisions will have supported ingame report
  functionality)
* Added division retention information. Initially, only the current month's recruitment data will be available. Soon,
  by-month query and removals will be available.

#### 09 Oct 2017 - 3.2.5
* Added ability to explore division notes, and filter by tag
* Tags and notes are now available when removing an individual member via their profile

#### 17 Sept 2017 - 3.2.4
* Added bb-code for sharing promotions on promotions reports page, also added a default select option to promotions
  period dropdown
* Fixed erroneous inactive (flagged) members view

#### 12 Sept 2017 - 3.2.3
* Ingame handle and part-time management has been moved out of the "Edit Member" area, and into their own areas. You can
  still navigate to these areas via the respective "Manage" buttons on a member profile. Additionally, junior leaders
  can now access these areas.
* Corrected bug where assigning a platoon leader who was previously a squad leader would not properly reset the losing
  squad.
* Ingame name field no longer auto-populates with forum name in recruitment process
* "Leaving page" warning no longer appears on the final step of the recruiting process.

#### 6 Sept 2017 - 3.2.2
* Added flagged member bb-code for sharing members removed
* Notes can now be added to Ex-AOD members.

#### 3 Sept 2017 - 3.2.1
* Minor logic issues fixed with assigning platoon leaders, squad leaders
* Removed deprecated member assignment functionality in Manage Platoon, Manage Squad views
* Members on leave now appear "dimmed" in member lists. Leave icon is now a grey color.
* Added a mass-pm button on part-timers management view
* Member profile page routes now include a slug that displays rank and name to make linking to member profiles more
  accessible.
* Users can now edit their own member profile, to manage position, handles, part-time status.
* Squad information now listed on the inactive members section. TS activity has been added to the flagged view.
* List division inactive by percentage instead of raw number. Number can still be viewed by hovering over the sparkline
  graph

#### 31 August 2017 - 3.2.0
* Slack management tools (for users, channels) implemented for division commanders and clan administration.
* Recruit information added to member profiles. Members without recruits will not have this section.
* Added member rank and name to profile view slug for improved deeplinking
* Notice added to pending member profiles regarding temporarily unavailable TS activity information

#### 23 August 2017 - 3.1.1
* Division census report updated to show a range of dates. Hover tooltip shows the exact day and member count. Notes
  have been omitted until a more viable solution is implemented. Graph ordering was also modified to appear left to
  right.

#### 22 August 2017 - 3.1.0
* Added application changelog
* Implemented `Reset member assignments` function on the member profile page to resolve erroneous platoon, squad
  assignments. This is for quickly resolving "disappearing" member issues, as well as convenience for members
  transferring out of a division.
* Added unassigned members area to division view, deprecating the unassigned member area in the platoon edit view. This
  will be removed in a later update.
    * Note: the manage members area deprecates the unassigned member in the squad edit view. This will also be removed
      at the same time.
* Fix issue where leave type isn't properly selected, or updated when editing an existing leave of absence.
* Add notice to member note when that note is associated with an existing leave of absence.
* Add pending member status attribute
    * New recruits will be flagged as pending to prevent the forum sync from erroneously resetting assignment
      information (because they are not listed as active). Pending member (and Ex-AOD) statuses will now reflect on the
      member profile page.
* Added member name variable `@{{ name }}` to division welcome PM

---

##### Lots of stuff from the before times that didn't make it to this list. If you're curious, check out [the changelog](https://github.com/ClanAODDev/tracker_v3/commits/main/)