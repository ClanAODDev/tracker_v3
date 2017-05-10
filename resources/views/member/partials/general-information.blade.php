<h4>Quick Info</h4><hr />
<div class="panel m-t-md panel-filled">

    <div class="panel-body">
        <div class="row">
            <div class="col-md-3 col-xs-6 text-center">
                <h4 class="no-margins">
                    {{ $member->last_activity->diffForHumans() }}
                </h4>
                Last <span class="c-white">Forum</span> Activity
            </div>

            <div class="col-md-3 col-xs-6 text-center">
                <h4 class="no-margins">
                    {{ $member->last_activity->diffForHumans() }}
                </h4>
                Last <span class="c-white">Teamspeak</span> Activity
            </div>

            <div class="col-md-3 col-xs-6 text-center">
                <h4 class="no-margins">
                    {{ $member->join_date }}
                </h4>
                Member <span class="c-white">Join</span> Date
            </div>

            <div class="col-md-3 col-xs-6 text-center">
                <h4 class="no-margins">
                    {{ $member->last_promoted }}
                </h4>
                Last <span class="c-white">Promotion</span> Date
            </div>
        </div>

    </div>
</div>
