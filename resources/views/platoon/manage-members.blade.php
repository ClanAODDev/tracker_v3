@extends('application.base-tracker')

@section('content')

    @component ('application.components.division-heading', ['division' => $division, 'logo' => $platoon->getLogoPath()])
        @slot ('heading')
            Manage {{ $division->locality('Squad') }} Assignments
        @endslot
        @slot ('subheading')
            {{ $platoon->name }} &middot; {{ $division->name }} Division
        @endslot
    @endcomponent

    <div class="container-fluid">
        {!! Breadcrumbs::render('platoon', $division, $platoon) !!}

        <div class="manage-assignments-section">
            <div class="manage-assignments-header">
                <div class="manage-assignments-info">
                    <p class="m-b-none">Drag members between {{ Str::plural($division->locality('squad')) }} to reassign them. Squad leaders cannot be reassigned from this view.</p>
                </div>
                <div class="manage-assignments-actions">
                    <a href="{{ route('platoon', [$division->slug, $platoon]) }}" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back to {{ $division->locality('platoon') }}
                    </a>
                    <a class="btn btn-success" href="{{ route('createSquad', [$division->slug, $platoon]) }}">
                        <i class="fa fa-plus"></i> Create {{ $division->locality('squad') }}
                    </a>
                </div>
            </div>

            @if (count($platoon->unassigned))
                <div class="unassigned-organizer m-t-lg">
                    <div class="unassigned-organizer-header">
                        <span>
                            <i class="fa fa-exclamation-triangle text-warning"></i>
                            {{ count($platoon->unassigned) }} {{ Str::plural('member', count($platoon->unassigned)) }} not assigned to a {{ $division->locality('squad') }}
                        </span>
                    </div>
                    <div class="unassigned-organizer-members mod-plt" style="display: block;">
                        <ul class="sortable manage-sortable-list">
                            @foreach ($platoon->unassigned as $member)
                                <li class="manage-draggable-member" data-member-id="{{ $member->id }}">
                                    {{ $member->present()->rankName }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="manage-squads-grid mod-plt sortable-squad m-t-lg">
                @foreach ($platoon->squads as $squad)
                    <div class="manage-squad-card" data-squad-id="{{ $squad->id }}">
                        <div class="manage-squad-header">
                            <div class="manage-squad-info">
                                <span class="manage-squad-name">{{ $squad->name ?? "Untitled" }}</span>
                                <span class="squad-stat-badge"><i class="fa fa-users"></i> <span class="count">{{ count($squad->members) }}</span></span>
                            </div>
                            <div class="manage-squad-leader">
                                @if ($squad->leader)
                                    {{ $squad->leader->present()->rankName }}
                                @else
                                    <span class="text-muted">TBA</span>
                                @endif
                            </div>
                        </div>
                        <ul class="sortable manage-sortable-list" data-squad-id="{{ $squad->id }}">
                            @foreach ($squad->members as $member)
                                <li class="manage-draggable-member" data-member-id="{{ $member->id }}">
                                    {{ $member->present()->rankName }}
                                    @if ($squad->leader && $squad->leader->clan_id == $member->recruiter_id)
                                        <i class="fa fa-asterisk text-accent" title="Direct Recruit"></i>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <div class="unassign-drop-zone m-t-lg">
                <div class="unassign-drop-zone-header">
                    <i class="fa fa-trash text-warning"></i>
                    <span>Unassign from {{ $division->locality('platoon') }}</span>
                </div>
                <div class="unassign-drop-zone-body mod-plt sortable-squad">
                    <ul class="sortable manage-sortable-list" data-squad-id="0"></ul>
                    <p class="unassign-drop-zone-hint">
                        <i class="fa fa-arrow-down"></i>
                        Drag members here to remove them from this {{ $division->locality('platoon') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer_scripts')
    @vite(['resources/assets/js/platoon.js'])
@endsection
