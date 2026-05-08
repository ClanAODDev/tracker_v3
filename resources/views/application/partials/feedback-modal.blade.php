<div class="modal fade" id="feedback-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">Share Feedback</h4>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    For bugs or support requests, use the
                    <a href="{{ route('help.tickets.widget') }}">Help Center</a>.
                </p>
                <div class="form-group">
                    <textarea id="feedback-body" class="form-control" rows="5"
                              placeholder="Share your thoughts, suggestions, or ideas..."
                              maxlength="2000"
                              style="resize:vertical;"></textarea>
                    <p class="text-right text-muted small" style="margin-top:4px; margin-bottom:0;">
                        <span id="feedback-char-count">0</span> / 2000
                    </p>
                </div>
                <div class="feedback-options">
                    <div class="feedback-options-row">
                        <label class="feedback-option-label">
                            <input type="checkbox" id="feedback-include-url" checked>
                            Include current page URL
                        </label>
                        <div class="feedback-screenshot-row">
                            <span class="text-muted small">Max 3 screenshots</span>
                            <button type="button" id="feedback-capture-btn" class="btn btn-sm btn-default">
                                <i class="fa fa-crop"></i> Take Screenshot
                            </button>
                        </div>
                    </div>
                    <div id="feedback-thumbnails"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="feedback-submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</div>

<div id="feedback-selection-overlay" style="display:none;">
    <div class="feedback-overlay-hint">Click and drag to select an area &mdash; <kbd>Esc</kbd> to cancel</div>
    <div id="feedback-selection-rect"></div>
</div>

<div id="feedback-preview-overlay" style="display:none;">
    <button type="button" id="feedback-preview-close" title="Close"><i class="fa fa-times"></i></button>
    <img id="feedback-preview-img" src="" alt="Screenshot preview">
</div>
