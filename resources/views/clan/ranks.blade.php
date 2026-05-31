@extends('application.base-tracker')

@section('content')

    @component('application.components.view-heading')
        @slot('currentPage') Clan Information @endslot
        @slot('icon') <i class="pe page-header-icon pe-7s-medal"></i> @endslot
        @slot('heading') Angels of Death @endslot
        @slot('subheading') Ranking structure, titles, and officer duties @endslot
    @endcomponent

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">

                <div class="rank-section-header">
                    <h4><i class="fa fa-sort-amount-desc"></i> Rank Ladder</h4>
                </div>

                <p class="text-muted" style="font-size:13px;margin-bottom:18px;">
                    AOD uses a military-style rank system. Click any rank to see its duties and responsibilities.
                </p>

                <div class="rank-ladder" id="rankLadder">

                    @php
                        $ranks = [
                            [
                                'abbr'   => 'SgtMaj',
                                'name'   => 'Sergeant Major',
                                'color'  => '#F09C58',
                                'width'  => 20,
                                'duties' => 'Overall leader of Clan AOD, responsible for handling issues within the clan and representing AOD when working with other clan leaders. All major decisions, policy changes, and high-ranking promotions must pass through the Sergeant Major.',
                            ],
                            [
                                'abbr'   => 'CmdSgt',
                                'name'   => 'Command Sergeant',
                                'color'  => '#CFCF0A',
                                'width'  => 26,
                                'duties' => 'High-ranking clan leader who assists the SgtMajs in the leadership of AOD. Instructs clan management across all divisions, supports lower-ranking officers with division issues, and is involved in forum administration, division creation and deletion, Sergeant promotions, and policy changes.',
                            ],
                            [
                                'abbr'   => '1stSgt',
                                'name'   => 'First Sergeant',
                                'color'  => '#00CFCF',
                                'width'  => 32,
                                'duties' => 'High-ranking admin who oversees a number of divisions, assisting members of all ranks. First Sergeants are expected to aid in division decisions and leadership, and to participate in clan-wide decision making.',
                            ],
                            [
                                'abbr'   => 'MSgt',
                                'name'   => 'Master Sergeant',
                                'color'  => '#AA00DD',
                                'width'  => 38,
                                'duties' => 'Experienced Sergeant active across a number of divisions, serving as informant and advisor. Master Sergeants also oversee the communication of all Sergeant promotions.',
                            ],
                            [
                                'abbr'   => 'SSgt',
                                'name'   => 'Staff Sergeant',
                                'color'  => '#2E2EFE',
                                'width'  => 44,
                                'duties' => 'Experienced Sergeant, commonly serving as CO or XO. Staff Sergeants are expected to aid newer Sergeants and lower-ranking officers. To be eligible for promotion, a Staff Sergeant should be active in more than one division, acting as an advisor and informant when requested by AOD leadership.',
                            ],
                            [
                                'abbr'   => 'Sgt',
                                'name'   => 'Sergeant',
                                'color'  => '#1A9900',
                                'width'  => 50,
                                'duties' => 'Member who has recently received the Sergeant rank. Acts as a squad leader within their full-time division. Upon reaching Sergeant, members gain access to ClanAOD.net and AOD TeamSpeak moderation tools, and become eligible for the Commanding Officer position.',
                            ],
                        ];

                        $enlisted = [
                            [
                                'abbr'   => 'Cpl',
                                'name'   => 'Corporal',
                                'color'  => '#B8C0C8',
                                'width'  => 57,
                                'duties' => 'Division squad leader. Not yet permitted to promote independently, a Corporal is responsible for their five recruits and leads their squad under their Sergeant\'s guidance. During this period, the Corporal is trained toward the Sergeant rank, learning squad management alongside their Sgt.',
                            ],
                            [
                                'abbr'   => 'LCpl',
                                'name'   => 'Lance Corporal',
                                'color'  => '#B8C0C8',
                                'width'  => 63,
                                'duties' => 'Experienced member. Sergeants within divisions begin training Lance Corporals for the leadership roles they will assume upon promotion to Corporal. LCpls assist squad leaders with tasks as needed and continue helping their recruits settle into the clan.',
                            ],
                            [
                                'abbr'   => 'TR',
                                'name'   => 'Trainer',
                                'color'  => '#B8C0C8',
                                'width'  => 67,
                                'duties' => '[Division Optional] An experienced member who has demonstrated individual ability and has been granted preliminary officer roles to begin recruiting. Their primary focus is helping all new members within their division become productive contributors to the clan.',
                            ],
                            [
                                'abbr'   => 'Spec',
                                'name'   => 'Specialist',
                                'color'  => '#B8C0C8',
                                'width'  => 71,
                                'duties' => 'Experienced member with demonstrated skill and game knowledge. A well-tenured member with broad clan experience — the rank most called upon to mentor others within their division.',
                            ],
                            [
                                'abbr'   => 'Pfc',
                                'name'   => 'Private First Class',
                                'color'  => '#B8C0C8',
                                'width'  => 76,
                                'duties' => 'Expected to help fellow clan members and set an example for incoming members. Rank is based on merit and time served. At this stage, a member should know and follow the Code of Conduct and strive to be the best representation of AOD they can be.',
                            ],
                            [
                                'abbr'   => 'Pvt',
                                'name'   => 'Private',
                                'color'  => '#B8C0C8',
                                'width'  => 82,
                                'duties' => 'Rank based on merit and time in AOD. A Private should post regularly on the forums and consistently demonstrate clan loyalty and honor.',
                            ],
                            [
                                'abbr'   => 'Cdt',
                                'name'   => 'Cadet',
                                'color'  => '#B8C0C8',
                                'width'  => 88,
                                'duties' => 'Active member who has demonstrated loyalty and honor in their first months in the clan.',
                            ],
                            [
                                'abbr'   => 'Rct',
                                'name'   => 'Recruit',
                                'color'  => '#B8C0C8',
                                'width'  => 96,
                                'duties' => 'New member of AOD. Should begin learning the Code of Conduct and the clan rank system, and be active in both the division\'s AOD server and on the forums.',
                            ],
                        ];
                    @endphp

                    <div class="rank-bar-group">
                        <div class="rank-tier-divider"><span>Clan Leadership &amp; Officers</span></div>
                        @foreach($ranks as $i => $rank)
                            @php $id = 'rank-' . $rank['abbr']; $delay = round(($i + 1) * 0.05, 2); @endphp
                            <div class="rank-bar animate-fade-in-up"
                                 style="width:{{ $rank['width'] }}%; background-color:{{ $rank['color'] }}; animation-delay:{{ $delay }}s;"
                                 data-target="{{ $id }}"
                                 role="button"
                                 tabindex="0"
                                 aria-expanded="false">
                                <span class="rank-bar-abbr">{{ $rank['abbr'] }}</span>
                                <span class="rank-bar-name">{{ $rank['name'] }}</span>
                            </div>
                            <div class="rank-duties-panel" id="{{ $id }}">
                                <div class="rank-duties-title">{{ $rank['name'] }}</div>
                                <p>{{ $rank['duties'] }}</p>
                            </div>
                        @endforeach

                        <div class="rank-tier-divider" style="margin-top:14px;"><span>Enlisted</span></div>

                        @foreach($enlisted as $i => $rank)
                            @php $id = 'rank-' . $rank['abbr']; $delay = round((count($ranks) + $i + 1) * 0.05, 2); @endphp
                            <div class="rank-bar animate-fade-in-up"
                                 style="width:{{ $rank['width'] }}%; background-color:{{ $rank['color'] }}; animation-delay:{{ $delay }}s;"
                                 data-target="{{ $id }}"
                                 role="button"
                                 tabindex="0"
                                 aria-expanded="false">
                                <span class="rank-bar-abbr">{{ $rank['abbr'] }}</span>
                                <span class="rank-bar-name">{{ $rank['name'] }}</span>
                            </div>
                            <div class="rank-duties-panel" id="{{ $id }}">
                                <div class="rank-duties-title">{{ $rank['name'] }}</div>
                                <p>{{ $rank['duties'] }}</p>
                            </div>
                        @endforeach
                    </div>

                </div>

                <div class="promotion-note">
                    <i class="fa fa-info-circle"></i>
                    <p>Only Sergeants and above may issue promotions or demotions, and these are given solely for good service to AOD. Promotions to CO or XO positions and all Sergeant-level promotions require approval from clan leadership.</p>
                </div>

                <div class="rank-section-header" style="margin-top:30px;">
                    <h4><i class="fa fa-star-o"></i> Titles</h4>
                </div>

                <p class="text-muted" style="font-size:13px;margin-bottom:18px;">
                    Titles are assigned positions that carry authority within a specific division. They are separate from rank and require nomination and approval by clan leadership.
                </p>

                <div class="title-cards">
                    <div class="title-card">
                        <div class="title-card-header">
                            <span class="title-card-abbr">CO</span>
                            <span class="title-card-label">Commanding Officer</span>
                        </div>
                        <div class="title-card-body">
                            <p>Awarded to members accepted to lead a division. COs run their division as they see fit within AOD's Code of Conduct and policies. This title carries no extra authority outside of the officer's own division.</p>
                            <p><strong>Minimum rank:</strong> Sergeant</p>
                        </div>
                    </div>
                    <div class="title-card">
                        <div class="title-card-header">
                            <span class="title-card-abbr">XO</span>
                            <span class="title-card-label">Executive Officer</span>
                        </div>
                        <div class="title-card-body">
                            <p>Nominated by the division CO and approved by clan leadership to assist in leading a division. XOs support the CO in all aspects of division leadership and take command in the CO's absence.</p>
                            <p><strong>Minimum rank:</strong> Corporal</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

@endsection

@section('footer_scripts')
<script>
$(function () {
    $('#rankLadder').on('click keydown', '[data-target]', function (e) {
        if (e.type === 'keydown' && e.which !== 13 && e.which !== 32) return;
        e.preventDefault();

        var targetId = $(this).data('target');
        var $panel   = $('#' + targetId);
        var $bar     = $(this);
        var isOpen   = $panel.hasClass('active');

        $('.rank-duties-panel.active').not($panel).removeClass('active');
        $('.rank-bar.rank-bar--active').not($bar).removeClass('rank-bar--active').attr('aria-expanded', 'false');

        $panel.toggleClass('active', !isOpen);
        $bar.toggleClass('rank-bar--active', !isOpen).attr('aria-expanded', !isOpen ? 'true' : 'false');
    });
});
</script>
@endsection
