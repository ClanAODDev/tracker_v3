<div class="panel panel-filled">
    <div class="panel-heading">
        <i class="fa fa-exclamation-circle"></i>
        Member Information
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-6">Member Name</div>
            <div class="col-sm-6">
                <code>AOD_Rct_{{ $request->forum_name }}</code>
                @include ('application.components.copy-button', ['data' => "AOD_Rct_" . $request->forum_name])
            </div>
        </div>
    </div>
</div>