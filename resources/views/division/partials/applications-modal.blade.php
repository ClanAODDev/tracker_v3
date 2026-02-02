<div class="modal fade" id="applicationsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                <h4 class="modal-title">
                    <i class="fab fa-discord" style="color: #5865F2;"></i>
                    Pending Applications
                </h4>
            </div>
            <div class="modal-body" id="applications-modal-body">
                <div class="text-center" id="applications-loading">
                    <span class="themed-spinner"></span> Loading applications...
                </div>
                <div id="applications-content" class="hidden">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="list-group" id="applications-list"></div>
                        </div>
                        <div class="col-md-8" id="applications-detail"></div>
                    </div>
                </div>
                <div id="applications-empty" class="hidden text-center" style="padding: 40px 20px;">
                    <i class="fab fa-discord" style="font-size: 32px; color: var(--color-muted);"></i>
                    <h4 style="margin-top: 12px;">No Pending Applications</h4>
                    <p class="text-muted">There are no pending Discord applications for this division.</p>
                </div>
            </div>
            <div class="modal-footer">
                <p class="text-muted" style="font-size: 12px; margin: 0; text-align: left; width: 100%;">
                    Applications are removed upon recruitment or after 30 days.
                </p>
            </div>
        </div>
    </div>
</div>
