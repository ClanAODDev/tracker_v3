@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division])
        @slot ('heading')
            Part-Timers
        @endslot
        @slot ('subheading')
            {{ $division->name }}
        @endslot
    @endcomponent

    <div class="container-fluid">

        {!! Breadcrumbs::render('part-timers', $division) !!}

        @include('division.partials.select-panel')

        <div class="inactive-stats">
            <div class="inactive-stat">
                <div class="inactive-stat-value">{{ $stats['total'] }}</div>
                <div class="inactive-stat-label">Total</div>
            </div>
            <div class="inactive-stat inactive-stat--success">
                <div class="inactive-stat-value">{{ $stats['active'] }}</div>
                <div class="inactive-stat-label">Active</div>
            </div>
            <div class="inactive-stat inactive-stat--info">
                <div class="inactive-stat-value">{{ $stats['onLeave'] }}</div>
                <div class="inactive-stat-label">On Leave</div>
            </div>
            <div class="inactive-stat inactive-stat--danger">
                <div class="inactive-stat-value">{{ $stats['removed'] }}</div>
                <div class="inactive-stat-label">Removed</div>
            </div>
            @if(auth()->user()->isRole(['officer', 'sr_ldr']))
                <button type="button" class="inactive-stat inactive-stat--action" data-toggle="modal" data-target="#add-parttimer-modal">
                    <div class="inactive-stat-value"><i class="fa fa-plus"></i></div>
                    <div class="inactive-stat-label">Add Part-Timer</div>
                </button>
            @endif
        </div>

        @if (count($members))
            <div class="inactive-toolbar">
                <div class="inactive-filters">
                    <span class="inactive-filter-label">
                        <i class="fa fa-users"></i> {{ count($members) }} part-time members
                    </span>
                </div>

                <div class="inactive-search-wrapper">
                    <i class="fa fa-search inactive-search-icon"></i>
                    <input type="text"
                           id="parttimer-search"
                           placeholder="Search members..."
                           class="inactive-search-input">
                </div>
            </div>

            <div class="inactive-content" style="border-radius: 8px;">
                <div class="inactive-panel active">
                    <div class="table-responsive">
                        <table class="table inactive-table basic-datatable for-pm-selection">
                            <thead>
                            <tr>
                                <th>Member</th>
                                <th>Primary Division</th>
                                <th>Ingame Name</th>
                                <th>Status</th>
                                <th class="no-sort text-right">Actions</th>
                                <th class="col-hidden">Clan Id</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($members as $member)
                                @php
                                    $rowClass = '';
                                    if ($member->division_id === 0) {
                                        $rowClass = 'inactive-row--severe';
                                    } elseif ($member->leave) {
                                        $rowClass = 'inactive-row--warning';
                                    }
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>
                                        <a href="{{ route('member', $member->getUrlParams()) }}" class="inactive-member-link">
                                            <span class="inactive-member-name">{{ $member->name }}</span>
                                            <span class="inactive-member-rank">{{ $member->rank->getAbbreviation() }}</span>
                                        </a>
                                    </td>
                                    <td>
                                        @if ($member->division_id > 0)
                                            <span class="inactive-unit">{{ $member->division->name }}</span>
                                        @else
                                            <span class="text-danger">None</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($member->handle)
                                            <code class="inactive-time">{{ $member->handle->pivot->value }}</code>
                                            @if ($member->handle->url)
                                                <a href="{{ $member->handle->url }}{{ $member->handle->pivot->value }}"
                                                   target="_blank" class="text-muted">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($member->division_id === 0)
                                            <span class="badge badge-danger">
                                                <i class="fa fa-times"></i> Removed
                                            </span>
                                        @elseif ($member->leave)
                                            <span class="badge badge-info">
                                                <i class="fa fa-calendar"></i> On Leave
                                            </span>
                                        @else
                                            <span class="badge badge-success">Active</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="inactive-actions">
                                            @can ('recruit', App\Models\Member::class)
                                                <a class="btn btn-sm btn-danger"
                                                   href="{{ route('removePartTimer', [$division->slug, $member->clan_id]) }}"
                                                   title="Remove from part-timers">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                    <td class="col-hidden">{{ $member->clan_id }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="inactive-content" style="border-radius: 8px;">
                <div class="inactive-empty">
                    <i class="fa fa-users" style="color: var(--color-muted);"></i>
                    <h4>No Part-Time Members</h4>
                    <p>This division currently has no part-time members assigned.<br>
                    To assign a member, find their profile, edit their member information,<br>
                    and select this division in the part-time tab.</p>
                </div>
            </div>
        @endif
    </div>

    @if(auth()->user()->isRole(['officer', 'sr_ldr']))
        <div class="modal fade" id="add-parttimer-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">Add Part-Time Member</h4>
                    </div>
                    <form id="add-parttimer-form" action="{{ route('addPartTimer', $division) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="parttimer-member-search">Search Member</label>
                                <input type="text"
                                       id="parttimer-member-search"
                                       class="form-control search-member"
                                       placeholder="Type member name..."
                                       autocomplete="off">
                                <input type="hidden" name="member_id" id="parttimer-member-id">
                                <div id="parttimer-selected-member" class="selected-member-display" style="display: none;">
                                    <span class="selected-member-name"></span>
                                    <button type="button" class="btn btn-xs btn-link clear-selected-member">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            @if($division->handle)
                                <div class="form-group">
                                    <label for="parttimer-handle-value">
                                        {{ $division->handle->label }} Handle
                                        <span class="text-muted">(optional)</span>
                                    </label>
                                    <input type="text"
                                           name="handle_value"
                                           id="parttimer-handle-value"
                                           class="form-control"
                                           placeholder="Enter their {{ strtolower($division->handle->label) }} name...">
                                    <p class="help-block text-muted">
                                        This will set their in-game handle for {{ $division->name }}
                                    </p>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <span class="modal-save-status"></span>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-accent" id="add-parttimer-submit" disabled>
                                <i class="fa fa-plus"></i> Add Part-Timer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

@endsection
