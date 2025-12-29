<div id="inactive-bulk-bar" class="bulk-action-bar" style="display: none;">
    <div class="bulk-action-bar-content">
        <span class="status-text"></span>
        <div class="actions">
            <form action="{{ route('private-message.create', compact('division')) }}" method="POST" class="bulk-pm-form">
                <input type="hidden" id="inactive-pm-member-data" name="pm-member-data">
                @csrf
                <button type="submit" class="btn btn-default">
                    <i class="fa fa-bullhorn text-accent"></i> <span class="hidden-xs hidden-sm">Send PM</span>...
                </button>
            </form>
            <button type="button" id="inactive-bulk-reminder-btn" class="btn btn-default"
                    data-url="{{ route('bulk-reminder.store', $division) }}"
                    title="Mark as reminded">
                <i class="fa fa-bell text-accent"></i> <span class="hidden-xs hidden-sm">Reminder</span>
            </button>
            @can('flag-inactive', \App\Models\Member::class)
                <button type="button" id="inactive-bulk-flag-btn" class="btn btn-warning"
                        data-url="{{ route('inactive.bulk-flag', $division) }}"
                        title="Flag selected members for removal">
                    <i class="fa fa-flag"></i> <span class="hidden-xs hidden-sm">Flag</span>
                </button>
            @endcan
            <button type="button" class="btn btn-link inactive-bulk-close" title="Clear selection">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
</div>

<div id="flagged-bulk-bar" class="bulk-action-bar" style="display: none;">
    <div class="bulk-action-bar-content">
        <span class="status-text"></span>
        <div class="actions">
            @can('flag-inactive', \App\Models\Member::class)
                <button type="button" id="flagged-bulk-unflag-btn" class="btn btn-warning"
                        data-url="{{ route('inactive.bulk-unflag', $division) }}"
                        title="Unflag selected members">
                    <i class="fa fa-flag"></i> <span class="hidden-xs hidden-sm">Unflag</span>
                </button>
            @endcan
            <button type="button" class="btn btn-link inactive-bulk-close" title="Clear selection">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
</div>
