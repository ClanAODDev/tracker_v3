<div class="modal fade" id="session-timeout-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="sessionTimeoutModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="sessionTimeoutModalLabel"><i class="fa fa-clock-o"></i> Session Timeout</h4>
            </div>
            <div class="modal-body">
                <div id="session-timeout-warning">
                    <p>Your session is about to expire due to inactivity.</p>
                    <p>You'll be signed out in <strong id="session-timeout-countdown">2:00</strong> unless you stay signed in.</p>
                </div>
                <div id="session-timeout-expired" style="display:none;">
                    <p>Your session has expired. Please log in again to continue.</p>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('login') }}" id="session-timeout-login" class="btn btn-primary" style="display:none;">Log In Again</a>
                <button type="button" id="session-timeout-stay" class="btn btn-primary">Stay Signed In</button>
            </div>
        </div>
    </div>
</div>
