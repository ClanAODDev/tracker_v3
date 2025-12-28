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
            <button type="button" class="btn btn-link inactive-bulk-close" title="Clear selection">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
</div>
