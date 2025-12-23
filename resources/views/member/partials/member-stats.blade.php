<div class="member-profile-section">
    <div class="row member-stat-row">
        <div class="col-lg-3 col-md-6 col-xs-12">
            <div class="panel panel-filled member-stat-card stat-tenure" data-toggle="modal" data-target="#tenure-modal" style="cursor: pointer;">
                <div class="stat-indicator"></div>
                <div class="panel-body">
                    <div class="stat-icon">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            {{ $memberStats->tenure->years }}<span class="stat-unit">y</span>
                            @if($memberStats->tenure->months > 0)
                                {{ $memberStats->tenure->months }}<span class="stat-unit">m</span>
                            @endif
                        </div>
                        <div class="stat-label">Time in AOD</div>
                        <div class="stat-detail text-muted">
                            Joined {{ $memberStats->tenure->joinDate?->format('M j, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-xs-12">
            <div class="panel panel-filled member-stat-card stat-activity stat-{{ $memberStats->activity->health }}">
                <div class="stat-indicator"></div>
                <div class="panel-body">
                    <div class="stat-icon">
                        <i class="fa fa-microphone"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            @if($memberStats->activity->daysSinceVoice !== null)
                                {{ $memberStats->activity->daysSinceVoice }}<span class="stat-unit">d</span>
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </div>
                        <div class="stat-label">Since Voice Activity</div>
                        <div class="stat-detail">
                            @if($memberStats->activity->daysSinceVoice !== null)
                                <div class="activity-bar">
                                    <div class="activity-fill activity-{{ $memberStats->activity->health }}"
                                         style="width: {{ $memberStats->activity->healthPct }}%"></div>
                                </div>
                                <small class="text-muted">{{ $memberStats->activity->divisionMax }}d threshold</small>
                            @else
                                <span class="text-muted">Never connected</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-xs-12">
            <div class="panel panel-filled member-stat-card stat-recruiting">
                <div class="stat-indicator"></div>
                <div class="panel-body">
                    <div class="stat-icon">
                        <i class="fa fa-user-plus"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ $memberStats->recruiting->total }}</div>
                        <div class="stat-label">Recruits</div>
                        <div class="stat-detail">
                            @if($memberStats->recruiting->total > 0)
                                <span class="text-success">{{ $memberStats->recruiting->active }} active</span>
                                @if($memberStats->recruiting->retentionRate !== null)
                                    <span class="text-muted">({{ $memberStats->recruiting->retentionRate }}% retention)</span>
                                @endif
                            @else
                                <span class="text-muted">No recruits yet</span>
                            @endif
                        </div>
                        @if($memberStats->recruiting->total > 0)
                            <button class="btn btn-xs btn-default stat-view-all" data-toggle="modal" data-target="#recruits-modal">
                                View all
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @can('create', App\Models\Note::class)
            <div class="col-lg-3 col-md-6 col-xs-12">
                <div class="panel panel-filled member-stat-card stat-notes {{ $noteStats->latestType ? 'latest-' . $noteStats->latestType : '' }}"
                     data-toggle="modal" data-target="#notes-modal" style="cursor: pointer;">
                    <div class="stat-indicator"></div>
                    <div class="panel-body">
                        <div class="stat-icon">
                            <i class="fa fa-sticky-note"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ $noteStats->total }}</div>
                            <div class="stat-label">Notes</div>
                            <div class="stat-detail">
                                @if($noteStats->total > 0)
                                    <div class="note-type-counts">
                                        @if($noteStats->positive > 0)
                                            <span class="note-count positive" title="Positive">
                                                <i class="fa fa-thumbs-up"></i> {{ $noteStats->positive }}
                                            </span>
                                        @endif
                                        @if($noteStats->negative > 0)
                                            <span class="note-count negative" title="Negative">
                                                <i class="fa fa-thumbs-down"></i> {{ $noteStats->negative }}
                                            </span>
                                        @endif
                                        @if($noteStats->misc > 0)
                                            <span class="note-count misc" title="General">
                                                <i class="fa fa-comment"></i> {{ $noteStats->misc }}
                                            </span>
                                        @endif
                                        @if($noteStats->sr_ldr > 0)
                                            <span class="note-count sr_ldr" title="Sr Leader">
                                                <i class="fas fa-shield-alt"></i> {{ $noteStats->sr_ldr }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">No notes recorded</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>

    @if($memberStats->recruiting->total > 0)
        <div class="modal fade" id="recruits-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">Recruits ({{ $memberStats->recruiting->total }})</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table basic-datatable">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Join Date</th>
                                    <th>Primary Division</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($member->recruits as $recruit)
                                    <tr>
                                        <td>
                                            <a href="{{ route('member', $recruit->getUrlParams()) }}">
                                                {{ $recruit->present()->rankName }}
                                            </a>
                                        </td>
                                        <td>{{ $recruit->join_date?->format('M j, Y') }}</td>
                                        <td>{{ $recruit->division->name ?? "Ex-AOD" }}</td>
                                        <td>
                                            @if($recruit->division)
                                                <span class="label label-success">Active</span>
                                            @else
                                                <span class="label label-default">Inactive</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(!auth()->user()->isRole('member'))
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-filled">
                    <div class="panel-heading">
                        <i class="fa fa-line-chart text-accent"></i> Rank Progression
                        @if($rankTimeline->historyItems->count() > 1)
                            <button class="btn btn-xs btn-default pull-right" data-toggle="modal" data-target="#rank-history-modal">
                                <i class="fa fa-list"></i> Full History
                            </button>
                        @endif
                    </div>
                    <div class="panel-body">
                        @include('member.partials.rank-timeline')
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($memberStats->divisionComparison)
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-filled division-comparison-panel">
                    <div class="panel-heading">
                        <i class="fa fa-bar-chart text-accent"></i> Division Comparison
                        <span class="text-muted pull-right">vs {{ $division->name }} average</span>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="comparison-metric">
                                    <div class="comparison-header">
                                        <span class="comparison-label">Tenure</span>
                                        <span class="comparison-percentile {{ $memberStats->divisionComparison->tenureBetter ? 'text-success' : '' }}">
                                            Top {{ max(1, 100 - $memberStats->divisionComparison->tenurePercentile) }}%
                                        </span>
                                    </div>
                                    <div class="comparison-bar-container">
                                        <div class="comparison-bar">
                                            <div class="comparison-avg" style="left: 50%;" title="Division avg: {{ $memberStats->divisionComparison->avgTenureYears }}y"></div>
                                            <div class="comparison-member {{ $memberStats->divisionComparison->tenureBetter ? 'better' : 'worse' }}"
                                                 style="left: {{ min(95, max(5, $memberStats->divisionComparison->tenurePercentile)) }}%"
                                                 title="Your tenure: {{ $memberStats->tenure->years }}y {{ $memberStats->tenure->months }}m"></div>
                                        </div>
                                    </div>
                                    <div class="comparison-legend">
                                        <span>Newer</span>
                                        <span>Division avg: {{ $memberStats->divisionComparison->avgTenureYears }}y</span>
                                        <span>Longer</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="comparison-metric">
                                    <div class="comparison-header">
                                        <span class="comparison-label">Voice Activity</span>
                                        <span class="comparison-percentile {{ $memberStats->divisionComparison->activityBetter ? 'text-success' : '' }}">
                                            @if($memberStats->activity->daysSinceVoice !== null)
                                                Top {{ max(1, 100 - $memberStats->divisionComparison->activityPercentile) }}%
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </div>
                                    <div class="comparison-bar-container">
                                        <div class="comparison-bar">
                                            <div class="comparison-avg" style="left: 50%;" title="Division avg: {{ $memberStats->divisionComparison->avgVoiceDays }}d"></div>
                                            @if($memberStats->activity->daysSinceVoice !== null)
                                                <div class="comparison-member {{ $memberStats->divisionComparison->activityBetter ? 'better' : 'worse' }}"
                                                     style="left: {{ min(95, max(5, $memberStats->divisionComparison->activityPercentile)) }}%"
                                                     title="Your activity: {{ $memberStats->activity->daysSinceVoice }}d ago"></div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="comparison-legend">
                                        <span>Less Active</span>
                                        <span>Division avg: {{ $memberStats->divisionComparison->avgVoiceDays }}d</span>
                                        <span>More Active</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="modal fade" id="tenure-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title">Membership Details</h4>
                </div>
                <div class="modal-body">
                    <div class="tenure-detail-list">
                        <div class="tenure-detail-item">
                            <span class="tenure-detail-label">Time in AOD</span>
                            <span class="tenure-detail-value">
                                {{ $memberStats->tenure->years }} year{{ $memberStats->tenure->years !== 1 ? 's' : '' }}
                                @if($memberStats->tenure->months > 0)
                                    {{ $memberStats->tenure->months }} month{{ $memberStats->tenure->months !== 1 ? 's' : '' }}
                                @endif
                            </span>
                        </div>
                        <div class="tenure-detail-item">
                            <span class="tenure-detail-label">Join Date</span>
                            <span class="tenure-detail-value">{{ $memberStats->tenure->joinDate?->format('F j, Y') }}</span>
                        </div>
                        @if($member->recruiter && $member->recruiter_id !== 0)
                            <div class="tenure-detail-item">
                                <span class="tenure-detail-label">Recruited By</span>
                                <span class="tenure-detail-value">
                                    <a href="{{ route('member', $member->recruiter->getUrlParams()) }}">
                                        {{ $member->recruiter->present()->rankName }}
                                    </a>
                                </span>
                            </div>
                        @endif
                        <div class="tenure-detail-item">
                            <span class="tenure-detail-label">Forum ID</span>
                            <span class="tenure-detail-value">{{ $member->clan_id }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
