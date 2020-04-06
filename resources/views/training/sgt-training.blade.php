@extends('application.base')

@section('content')

    @component ('application.components.view-heading')
        @slot ('currentPage')
            Leadership Training
        @endslot
        @slot ('icon')
            <img src="{{ asset('images/logo_v2.svg') }}" width="50px"/>
        @endslot
        @slot ('heading')
            AOD Tracker
        @endslot
        @slot ('subheading')
            Training Module
        @endslot
    @endcomponent

    <div class="container-fluid" id="training-container">

        @include('application.partials.errors')

        <div class="row">
            <div class="col-md-12">
                <h4>SGT Training</h4>
                <hr>

                <nav class="tabs-container">
                    <ul class="nav nav-tabs" id="nav-tab" role="tablist">
                        <li><a class="nav-item nav-link btn-default btn" data-toggle="tab"
                               href="#sgt-duties" role="tab"><i class="fas fa-check-circle text-muted"></i> Sgt
                                Duties</a></li>
                        <li><a class="nav-item nav-link btn-default btn" data-toggle="tab"
                               href="#sgt-structure" role="tab"><i class="fas fa-check-circle text-muted"></i> SGT
                                Structure</a></li>
                        <li><a class="nav-item nav-link btn-default btn" data-toggle="tab"
                               href="#forum-mod" role="tab"><i class="fas fa-check-circle text-muted"></i> Forum
                                Mod</a></li>
                        <li><a class="nav-item nav-link btn-default btn" data-toggle="tab"
                               href="#ts-mod" role="tab"><i class="fas fa-check-circle text-muted"></i> Teamspeak
                                Mod</a></li>
                        <li><a class="nav-item nav-link btn-default btn" data-toggle="tab"
                               href="#misc" role="tab"><i class="fas fa-check-circle text-muted"></i> Misc
                                Info</a></li>
                    </ul>
                </nav>

                <div class="panel panel-filled">
                    <div class="tab-content" id="nav-tabContent">

                        <div class="tab-pane p-md fade" id="sgt-duties" role="tabpanel">

                            <h4>Duties and responsibilities</h4>
                            <ul>
                                <li>Understand point #5. Leaving a division immediately after you have been promoted is
                                    strongly discouraged.
                                </li>
                                <li>Also understand that your area of responsibility as a SGT now
                                    expands beyond that of your division. Members from other divisions may come to you
                                    for
                                    assistance when their leadership is not available.
                                    <ul>
                                        <li>Take care not to step on any toes or make decisions
                                            that depart from that division's norms. Utilize the <a
                                                    href="https://www.clanaod.net/forums/showthread.php?t=79087">SGT
                                                Decision Log</a> when forced to take action, and document fully.
                                        </li>
                                        <li>Strive to be helpful but don't meddle or otherwise interfere.</li>
                                    </ul>
                                </li>
                            </ul>

                            <p class="m-t-lg">
                                <a href="http://www.clanaod.net/forums/showthread.php?t=51654"
                                   class="btn btn-primary"
                                   target="_blank"> Open Sgt Duties Thread <i
                                            class="fas fa-external-link-alt text-primary"></i>
                                </a>
                            </p>

                        </div>

                        <div class="tab-pane fade p-md" id="sgt-structure" role="tabpanel">
                            <h4>SGT Structure</h4>
                            <p>
                                Review both the Sgt structure and the SSgt lists below. Explain briefly what a SSgt's
                                role
                                is. They should come away with an understanding that
                                they are part of a highly organized structure, and that there is always someone there to
                                help if they get stuck.
                                This would be a good time to let them know that you will be getting back to them (not
                                checking on) in a week or
                                two to see if they have any further questions and see how they are adapting in their new
                                role.
                            </p>

                            <p class="m-t-lg">
                                <a href="https://tracker.clanaod.net/reports/sergeants"
                                   class="btn btn-primary"
                                   target="_blank"> Open Sgt Structure <i
                                            class="fas fa-external-link-alt text-primary"></i>
                                </a>
                                <a href="https://www.clanaod.net/forums/showthread.php?t=56367"
                                   class="btn btn-primary"
                                   target="_blank"> Open SSgt Duties <i
                                            class="fas fa-external-link-alt text-primary"></i>
                                </a>
                            </p></div>
                        <div class="tab-pane fade p-md" id="forum-mod" role="tabpanel">
                            <h4>Forum Moderation</h4>
                            <p>
                                Review how to make announcements on the forums.
                                Show them the Forum Manager link (a second way to post announcements (easier IMO)
                                Go over Search for users, Search IP addresses. Give examples of why you would use that
                                feature. Explain why you
                                would Edit AOD info (promotions, demotions). Remind them of the poor placement of the
                                "Remove Member" button and
                                to be careful.
                                Review the Ban User feature, Do not gloss over this. Spend some time discussing it.
                                Banning
                                is never an
                                emotional response. It is a tool to protect AOD from threats that could hinder the clan.
                                Make sure they
                                understand that they need to remove AOD membership status before banning. Make sure the
                                ban
                                is permanent and
                                that a reason is given for the ban. Discuss reversing a ban, and the does and don't.
                                When in
                                doubt seek higher
                                authority. Review a few bans in the Sgt section for appropriate structure.
                                Review award section and how to assign an award. Show them where the Request for new
                                Awards
                                sticky is in the Sgt
                                section.
                            </p>

                            <p class="m-t-lg">
                                <a href="https://www.clanaod.net/forums/modcp/index.php"
                                   class="btn btn-primary"
                                   target="_blank"> Open ModCP <i class="fas fa-external-link-alt text-primary"></i>
                                </a>
                            </p>
                        </div>

                        <div class="tab-pane fade p-md" id="ts-mod" role="tabpanel">
                            <h4>TS Moderation</h4>

                            <p>
                                Moving members - Ask permission first - Do not abuse
                                Kicking/Banning - Remind them that they cannot reverse a permanent ban
                                Adding Channels - As needed, make sure that they are temporary, and clean up after they
                                are
                                done with the
                                channel. CO/XO should be consulted before changing any permanent channel in the
                                Division's
                                area
                            </p>
                        </div>
                        <div class="tab-pane fade p-md" id="misc" role="tabpanel">
                            <h4>Misc Information</h4>
                            <p>
                                Point out that they can now see who has looked at a post on the forum. Remind them that
                                Taptalk does not update this data. Have them browse some of the other division forums
                                and
                                let them know that they can now see all divisions Sgt areas. Remind them that they are a
                                guest in those areas and to act appropriately. Point out that stealing good ideas from
                                other
                                divisions is encouraged, with credit given as appropriate. Discuss with CO/XO before
                                implementation, however.
                            </p>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-6">
                        <h4>SGT Handbook</h4>
                        <p>Do not do this ahead of time, or they will be distracted throughout the whole
                            process. Take some time to answer any questions that they may have. Set up a few times in a
                            week or
                            two to revisit and chat about their first few weeks as a Sgt. You will probably get some
                            great
                            stories.</p>
                        <p><a href="http://clanaod.net/files/AOD-SGT-HANDBOOK.pdf"
                              target="_blank"
                              class="btn btn-primary">AOD Sgt Handbook (PDF) <i
                                        class="fas fa-external-link-alt text-primary"></i>
                            </a>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-filled {{ $errors->has('clan_id') ? 'panel-c-danger' : null }}">
                            <div class="panel-heading">Confirm Training</div>
                            <div class="panel-body">
                                <p>By submitting this form, you confirm that training has been completed for this
                                    SGT</p>
                            </div>
                            <div class="panel-footer">

                                <form action="{{ route('training.update') }}" method="POST">
                                    {{ csrf_field() }}
                                    <table class="table table-bordered">
                                        <tr>
                                            <td>
                                                <input type="number"
                                                       class="form-control col-9"
                                                       id="clan_id"
                                                       name="clan_id"
                                                       placeholder="Enter Clan ID..." value="{{ old('clan_id') }}"></td>
                                            <td>
                                                <button type="submit" class="btn btn-default btn-block">Submit</button>
                                            </td>
                                        </tr>
                                    </table>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
@stop

@section('footer_scripts')
    <script>
        $('.nav-item').click(function () {
            $(this).find('.fa-check-circle').removeClass('text-muted').addClass('text-success');
        })
    </script>
@stop