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
            <div class="col-md-9">

                @php
                    use App\Enums\Rank;

                    $byGroup = collect(array_reverse(Rank::cases()))
                        ->groupBy(fn(Rank $r) => $r->getGroup());

                    $clanAdmin = $byGroup->get('admin', collect());
                    $officers  = $byGroup->get('officer', collect());
                    $enlisted  = $byGroup->get('enlisted', collect());
                @endphp

                <div class="rank-tree">

                    @php
                        $sections = [
                            ['label' => 'Clan Admin', 'ranks' => $clanAdmin],
                            ['label' => 'Officer',    'ranks' => $officers],
                            ['label' => 'Enlisted',   'ranks' => $enlisted],
                        ];
                        $overallIdx = 0;
                    @endphp

                    @foreach($sections as $si => $section)
                        <div class="rank-tree-divider{{ $si === 0 ? ' rank-tree-divider--first' : '' }}">
                            <span class="rank-tree-divider-label">{{ $section['label'] }}</span>
                        </div>

                        <div class="rank-tree-section">
                            <span class="rank-tree-side-label">{{ $section['label'] }}</span>
                            @foreach($section['ranks'] as $i => $rank)
                                @php
                                    $overallIdx++;
                                    $delay  = round($overallIdx * 0.05, 2);
                                    $isLast = $si === array_key_last($sections) && $i === $section['ranks']->keys()->last();
                                @endphp
                                <div class="rank-tree-item rank-tree-item--{{ $rank->getTier() }}{{ $isLast ? ' rank-tree-item--last' : '' }} animate-fade-in-up"
                                     style="animation-delay:{{ $delay }}s; --rank-color:{{ $rank->getColorHex() }};">
                                    <div class="rank-tree-side"></div>
                                    <div class="rank-tree-track">
                                        <div class="rank-tree-node"></div>
                                    </div>
                                    <div class="rank-tree-body">
                                        <div class="rank-tree-header">
                                            <span class="rank-tree-abbr">{{ $rank->getAbbreviation() }}</span>
                                            <span class="rank-tree-name">{{ $rank->getLabel() }}</span>
                                        </div>
                                        <p class="rank-tree-duties">{{ $rank->getDuties() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                </div>

            </div>

            <div class="col-md-3">
                <div class="rank-titles-sidebar">
                    <p class="text-muted" style="font-size:12px;margin-bottom:12px;">
                        Titles carry authority within a specific division, are separate from rank, and require nomination and approval by clan leadership.
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

                    <div class="promotion-note">
                        <i class="fa fa-info-circle"></i>
                        <p>Only Sergeants and above may issue promotions or demotions, and these are given solely for good service to AOD. Promotions to CO or XO positions and all Sergeant-level promotions require approval from clan leadership.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
