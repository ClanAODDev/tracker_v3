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
            <div class="modal-body">
                <div id="applications-widget-container"
                     data-url="{{ url('/api/divisions/' . $division->slug . '/applications') }}"
                     data-can-delete="{{ auth()->user()->isRole(['sr_ldr', 'admin']) ? 'true' : 'false' }}">
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
