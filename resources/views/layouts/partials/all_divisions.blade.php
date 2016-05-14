<div class="panel panel-default">
    <div class="panel-heading"><i class="fa fa-gamepad fa-lg pull-right text-muted"></i> <strong>All Gaming
            Divisions</strong> ({{ $divisions->count() }})
    </div>

    <div class='list-group'>

        @foreach ($divisions as $division)
            @if ($division->isEnabled())
                <a href="{{ action('DivisionController@show', [$division->abbreviation]) }}"
                   class="list-group-item"
                   style="padding-bottom: 18px;">
                    <span class="pull-left" style="margin-right: 20px; vertical-align: middle;"><img
                                class="img-responsive" src="images/game_icons/48x48/{{ $division->abbreviation }}.png"/></span>
                    <h4 class="list-group-item-heading hidden-xs hidden-sm">
                        <strong>{{ $division->name }}</strong>
                    </h4>

                    <h2 class="list-group-item-heading hidden-md hidden-lg"
                        style="margin:10px 0;">
                        {{ strtoupper($division->abbreviation) }}
                    </h2>

                    <p class="list-group-item-text text-muted hidden-xs hidden-sm">{{ $division->description }}</p>

                    <h5 class="pull-right text-muted big-num-main count-animated">{{ $division->members->count() }}</h5>
                </a>
            @endif
        @endforeach
    </div>
</div>