<div class="panel panel-filled">

    <div class="panel-body">
        <div class="row">
            <div class="col-md-3 col-xs-6 text-center">
                <h3 class="no-margins">
                    {{ $member->last_activity->diffForHumans() }}
                </h3>
                Last <span class="c-white">Forum</span> Activity
            </div>

            <div class="col-md-3 col-xs-6 text-center">
                <h3 class="no-margins">
                    {{ $member->last_activity->diffForHumans() }}
                </h3>
                Last <span class="c-white">Teamspeak</span> Activity
            </div>

            <div class="col-md-3 col-xs-6 text-center">
                <h3 class="no-margins">
                    {{ $member->join_date }}
                </h3>
                Member <span class="c-white">Join</span> Date
            </div>

            <div class="col-md-3 col-xs-6 text-center">
                <h3 class="no-margins">
                    {{ $member->last_promoted }}
                </h3>
                Last <span class="c-white">Promotion</span> Date
            </div>
        </div>

    </div>
</div>
