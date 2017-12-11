<div class="modal fade in" id="create-fireteam" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('fireteams.store') }}" method="post" id="create-fireteam-form">
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-body">
                    <h4 class="modal-title text-uppercase">Create Fireteam</h4>
                    <div class="form-group">
                        <label for="name">Title of fireteam</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="players_needed">Number of Players Needed</label>
                        {{ Form::selectRange('players_needed', 1, 5, 1, ['class' => 'form-control']) }}
                    </div>
                    <div class="form-group m-t-md row">
                        <div class="col-xs-6">
                            <label for="type">
                                Type of fireteam
                            </label>
                            <select name="type" id="type" class="form-control">
                                <option value="raid">Raid</option>
                                <option value="crucible">Crucible</option>
                                <option value="strikes">Strikes</option>
                                <option value="trials of the nine">Trials of the Nine</option>
                                <option value="down for anything">Down for anything</option>
                            </select>
                        </div>
                        <div class="col-xs-6">
                            <label for="light">
                                <span style="color: #41eacf">&#x2727;</span> Your light level
                            </label>
                            <input type="number" class="form-control" name="light" required />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Details</label>
                        <textarea name="description" id="description" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="m-t-md">
                        <small>By creating this fireteam, you agree to coordinate and communicate with fellow fireteam members. You will be notified when your fireteam is full.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Create Fireteam</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade in" id="join-fireteam" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <form action="#" method="post" id="join-fireteam-form">
            {{ method_field('PUT') }}
            {{ csrf_field() }}
            <div class="modal-content">
                <div class="modal-body">
                    <h4 class="modal-title text-uppercase">Join Fireteam</h4>
                    <div class="form-group m-t-md">
                        <label for="light">
                            <span style="color: #41eacf">&#x2727;</span> Your current light level
                        </label>
                        <input type="number" class="form-control" name="light" required />
                    </div>

                    <small class="form-group">
                        <p>By joining this fireteam, you agree to participate at the time the event is scheduled for. You will receive email notification when all slots are filled.</p>
                    </small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Join Fireteam</button>
                </div>
            </div>
        </form>
    </div>
</div>