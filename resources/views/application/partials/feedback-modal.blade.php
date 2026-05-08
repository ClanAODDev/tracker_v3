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
                              maxlength="2000"></textarea>
                </div>
                <p class="text-right text-muted small">
                    <span id="feedback-char-count">0</span> / 2000
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="feedback-submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</div>
