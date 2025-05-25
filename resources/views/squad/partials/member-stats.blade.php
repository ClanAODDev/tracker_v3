<div class="panel panel-filled">
    <div class="panel-body">
        <h1 class="text-center" style="margin: unset;">
            <i class="pe pe-7s-users pe-lg text-warning"></i> {{ $squad->members->count() }}
            <small class="slight">Members</small>
        </h1>
    </div>
</div>
{{--<div class="panel panel-filled hidden-xs hidden-sm">--}}
{{--    <div class="panel-heading">--}}
{{--        Forum Activity--}}
{{--    </div>--}}
{{--    <div class="panel-body">--}}
{{--        <canvas class="forum-activity-chart"--}}
{{--                data-labels="{{ json_encode($forumActivityGraph['labels']) }}"--}}
{{--                data-values="{{ json_encode($forumActivityGraph['values']) }}"--}}
{{--                data-colors="{{ json_encode($forumActivityGraph['colors']) }}"></canvas>--}}
{{--    </div>--}}
{{--</div>--}}

<div class="panel panel-filled hidden-xs hidden-sm">
    <div class="panel-heading">
        Voice Activity
    </div>
    <div class="panel-body">
        <canvas class="voice-activity-chart" data-labels="{{ json_encode($voiceActivityGraph['labels']) }}"
                data-values="{{ json_encode($voiceActivityGraph['values']) }}"
                data-colors="{{ json_encode($voiceActivityGraph['colors']) }}"></canvas>
    </div>
</div>