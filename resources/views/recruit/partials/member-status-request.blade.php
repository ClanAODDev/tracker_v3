<h4><i class="fa fa-plus-square text-accent"></i> Step 4: Member Status Request</h4>

<p>Your new recruit has been added to the Tracker, but you still need to submit a member status request to have their AOD permissions approved. To do that, use the button below to open the status request form.</p>

<p>Note: if you need to change the member's forum name, include it as the "name" in your request. Otherwise,
    <span class="text-accent">leave the name field blank</span>.</p>

<div class="row">
    <div class="col-sm-6 text-center">
        <label>Forum User ID</label>
        <code>{{ $request->member_id }}</code>
        @include ('application.components.copy-button', ['data' => $request->member_id])
    </div>

    <div class="col-sm-6 text-center">
        <div class="m-t-md visible-xs-block"></div>
        <a href="http://www.clanaod.net/forums/misc.php?do=form&fid=39" target="_blank"
           class="btn btn-accent">
            <i class="fa fa-external-link text-accent" aria-hidden="true"></i> Open Member Status Form
        </a>
    </div>
</div>

<hr />