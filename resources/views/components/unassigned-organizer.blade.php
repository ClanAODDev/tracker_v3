@props([
    'count',
    'unitType',
    'members',
    'buttonClass' => 'organize-btn',
    'membersClass' => 'unassigned-member',
    'containerClass' => 'unassigned-organizer',
    'memberIdField' => 'clan_id',
    'canOrganize' => true,
])

@if($count > 0)
    <div class="{{ $containerClass }}">
        <div class="{{ $containerClass }}-header">
            <span>
                <i class="fa fa-exclamation-triangle text-warning"></i>
                {{ $count }} {{ Str::plural('member', $count) }} not assigned to a {{ $unitType }}
            </span>
            @if($canOrganize)
                <button type="button" class="btn btn-sm btn-accent {{ $buttonClass }}">
                    <i class="fa fa-arrows-alt"></i> Organize
                </button>
            @endif
        </div>
        @if($canOrganize)
            <div class="{{ $containerClass }}-members" style="display: none;">
                @foreach($members as $member)
                    <div class="{{ $membersClass }}" data-member-id="{{ $member->{$memberIdField} }}">
                        {{ $member->present()->rankName }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
