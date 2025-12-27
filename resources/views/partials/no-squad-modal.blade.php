@if ($pendingActions->has('unassigned-to-squad'))
    <div class="modal fade" id="no-squad-modal" tabindex="-1" role="dialog"
         data-url="{{ route('division.unassigned-to-squad', $division) }}">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    <h4 class="modal-title">Members Without Squad Assignment</h4>
                </div>
                <div class="modal-body">
                    <div id="no-squad-loading" class="text-center text-muted">
                        <span class="themed-spinner"></span> Loading...
                    </div>
                    <div id="no-squad-list" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endif
