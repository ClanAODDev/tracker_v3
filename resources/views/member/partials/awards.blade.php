@if ($member->awards->count())
    @php
        $awardsImageUrl = url("members/{$member->clan_id}-{$member->rank->getAbbreviation()}-{$member->name}/my-awards.png");
        $clusterImageUrl = url("members/{$member->clan_id}-{$member->rank->getAbbreviation()}-{$member->name}/my-awards-cluster.png");
    @endphp
    <div class="achievements-header m-t-xl" id="achievements">
        <h4>
            Achievements
            <button type="button" class="btn btn-sm btn-default" data-toggle="modal" data-target="#share-awards-modal" title="Share awards">
                <i class="fa fa-share-alt"></i> Share
            </button>
        </h4>
        <div class="rarity-summary">
            @foreach(['mythic', 'legendary', 'epic', 'rare', 'common'] as $rarity)
                @if($memberStats->awards->byRarity->get($rarity, 0) > 0)
                    <span class="award-pill pill-{{ $rarity }}">
                        {{ $memberStats->awards->byRarity->get($rarity) }} {{ ucfirst($rarity) }}
                    </span>
                @endif
            @endforeach
        </div>
    </div>

    <div class="modal fade" id="share-awards-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title"><i class="fa fa-share-alt"></i> Share Awards</h4>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Use these links to embed award images in forum signatures or other places. Images are cached for {{ config('aod.awards.cache_minutes', 15) }} minutes.</p>

                    <div class="form-group">
                        <label>Banner Style (up to 4 awards)</label>
                        <div class="share-preview text-center" style="background: #1a1a2e; padding: 10px; border-radius: 4px; margin-bottom: 8px;">
                            <img src="{{ $awardsImageUrl }}?award_count=4" alt="Awards Banner" style="max-width: 100%; height: auto;">
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" readonly value="{{ $awardsImageUrl }}" id="awards-banner-url">
                            <span class="input-group-btn">
                                <button class="btn btn-default copy-url-btn" type="button" data-target="awards-banner-url" title="Copy URL">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </span>
                        </div>
                        <p class="help-block">
                            Options: <code>?award_count=1-4</code>, <code>&font_size=8-14</code>
                        </p>
                    </div>

                    <div class="form-group">
                        <label>Cluster Style (up to 6 awards)</label>
                        <div class="share-preview text-center" style="background: #1a1a2e; padding: 10px; border-radius: 4px; margin-bottom: 8px;">
                            <img src="{{ $clusterImageUrl }}" alt="Awards Cluster" style="max-width: 100%; height: auto;">
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" readonly value="{{ $clusterImageUrl }}" id="awards-cluster-url">
                            <span class="input-group-btn">
                                <button class="btn btn-default copy-url-btn" type="button" data-target="awards-cluster-url" title="Copy URL">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>BBCode (for forum signatures)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" readonly value="[img]{{ $awardsImageUrl }}[/img]" id="awards-bbcode">
                            <span class="input-group-btn">
                                <button class="btn btn-default copy-url-btn" type="button" data-target="awards-bbcode" title="Copy BBCode">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.copy-url-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = this.getAttribute('data-target');
                var input = document.getElementById(targetId);
                input.select();
                input.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(input.value).then(function() {
                    var icon = btn.querySelector('i');
                    icon.className = 'fa fa-check';
                    setTimeout(function() {
                        icon.className = 'fa fa-copy';
                    }, 1500);
                });
            });
        });
    </script>
    @php
        $memberAwardIds = $member->awards->pluck('award_id')->unique();

        $groupedAwards = $member->awards
            ->groupBy('award_id')
            ->map(function ($records) {
                return [
                    'award' => $records->first()->award,
                    'records' => $records->sortBy('created_at'),
                    'count' => $records->count(),
                    'latest' => $records->sortByDesc('created_at')->first(),
                ];
            });

        $skipAwardIds = collect();
        $tieredGroups = [];

        foreach ($groupedAwards as $awardId => $group) {
            $award = $group['award'];
            $chain = $award->getPrerequisiteChain();

            if (count($chain) > 0) {
                $earnedInChain = collect([$award])
                    ->merge($chain)
                    ->filter(fn($a) => $memberAwardIds->contains($a->id))
                    ->sortByDesc(fn($a) => count($a->getPrerequisiteChain()));

                $highest = $earnedInChain->first();
                if ($highest && $highest->id === $award->id) {
                    $tieredGroups[$awardId] = $earnedInChain->values()->all();
                    $skipAwardIds = $skipAwardIds->merge($earnedInChain->skip(1)->pluck('id'));
                }
            }
        }

        $displayAwards = $groupedAwards
            ->reject(fn($g, $id) => $skipAwardIds->contains($id))
            ->sortBy('award.display_order');
    @endphp
    <div class="row award-grid">
        @foreach ($displayAwards as $awardId => $group)
            @php
                $award = $group['award'];
                $count = $group['count'];
                $latestRecord = $group['latest'];
                $rarity = $award->getRarity();
                $dates = $group['records']->map(fn($r) => $r->created_at->format('M d, Y'))->join("\n");
                $tieredAwards = $tieredGroups[$awardId] ?? [];
                $isTiered = count($tieredAwards) > 1;
                $tieredSlug = $isTiered ? $award->getTieredGroupSlug() : null;
                $awardLink = $tieredSlug ? route('awards.tiered', $tieredSlug) : route('awards.show', $award);
            @endphp
            <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
                <a href="{{ $awardLink }}"
                   class="member-award-card member-award-card-{{ $rarity }}"
                   title="{{ $latestRecord->reason ?? $award->description }}">
                    @if($count > 1)
                        <span class="award-count-badge" data-toggle="tooltip" data-placement="top" data-html="true" title="Earned {{ $count }} times:<br>{{ nl2br(e($dates)) }}">
                            x{{ $count }}
                        </span>
                    @endif
                    <div class="rarity-indicator rarity-{{ $rarity }}"></div>
                    <div class="panel-body text-center">
                        @if($isTiered)
                            <div class="award-tier-stack" data-tier-count="{{ count($tieredAwards) }}" data-toggle="tooltip" data-placement="top" title="{{ collect($tieredAwards)->pluck('name')->join(', ') }}">
                                @foreach(array_reverse($tieredAwards) as $index => $tierAward)
                                    <div class="award-tier-item" style="z-index: {{ $index + 1 }};">
                                        @if($tierAward->image && Storage::disk('public')->exists($tierAward->image))
                                            <img src="{{ $tierAward->getImagePath() }}" alt="{{ $tierAward->name }}" loading="lazy" />
                                        @else
                                            <div class="award-tier-placeholder"><i class="fas fa-trophy"></i></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="award-image-wrapper">
                                @if($award->image && Storage::disk('public')->exists($award->image))
                                    <img src="{{ $award->getImagePath() }}"
                                         alt="{{ $award->name }}"
                                         class="clan-award" loading="lazy"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                                    />
                                    <div class="award-placeholder" style="display:none">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                @else
                                    <div class="award-placeholder">
                                        <i class="fas fa-trophy"></i>
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div class="award-card-name">{{ $award->name }}</div>
                        <span class="award-pill pill-{{ $rarity }}">{{ $latestRecord->created_at->format('M d, Y') }}</span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif