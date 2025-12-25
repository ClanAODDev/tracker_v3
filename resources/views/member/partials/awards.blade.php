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
                            Options: <code>?award_count=1-4</code>, <code>&font=ttf|bitmap</code>, <code>&font_size=7-12</code>
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
    <div class="row award-grid">
        @foreach ($member->awards->sortBy('award.display_order') as $record)
            @php $rarity = $record->award->getRarity(); @endphp
            <div class="col-lg-2 col-md-3 col-sm-4 col-xs-6">
                <a href="{{ route('awards.show', $record->award) }}"
                   class="member-award-card member-award-card-{{ $rarity }}"
                   title="{{ $record->reason ?? $record->award->description }}">
                    <div class="rarity-indicator rarity-{{ $rarity }}"></div>
                    <div class="panel-body text-center">
                        <div class="award-image-wrapper">
                            @if($record->award->image && Storage::disk('public')->exists($record->award->image))
                                <img src="{{ $record->award->getImagePath() }}"
                                     alt="{{ $record->award->name }}"
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
                        <div class="award-card-name">{{ $record->award->name }}</div>
                        <span class="award-pill pill-{{ $rarity }}">{{ $record->created_at->format('M d, Y') }}</span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endif