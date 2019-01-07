<h4>General Info</h4>
<hr />

<div class="row">

    @component('application.components.data-block')
        @slot('data') {{ $member->last_activity->diffInDays() }} days @endslot
        @slot('title')since last <span class="c-white">forum activity </span>@endslot
        @slot('color')
            @if($division)
                {{ $member->last_activity->diffInDays() > $division->settings()->inactivity_days ? "panel-c-danger" : null }}
            @endif
        @endslot
    @endcomponent

    @component('application.components.data-block')
        @slot('data')
            @if ($member->isPending)
                <span class="text-muted">UNAVAILABLE</span>
            @elseif ($member->tsInvalid)
                TS MISCONFIGURATION
            @else
                {{ Carbon::parse($member->last_ts_activity)->diffInDays() }} DAYS
            @endif
        @endslot
        @slot('color')
            {{ $member->tsInvalid ? "panel-c-danger" : null }}
        @endslot
        @slot('title')
            since last <span class="c-white">TS activity </span>
        @endslot
    @endcomponent

    @component('application.components.data-block')
        @slot('data') {{ $member->join_date }} @endslot
        @slot('title') Member <span class="c-white">join date</span> @endslot
    @endcomponent

    @component('application.components.data-block')
        @slot('data') {{ $member->present()->lastPromoted() }} @endslot
        @slot('title') Last <span class="c-white">promotion date</span> @endslot
    @endcomponent

    @component('application.components.link-block')
        @slot('link')
            https://www.clanaod.net/forums/search.php?do=finduser&amp;userid={{ $member->clan_id }}&amp;contenttype=vBForum_Post&amp;showposts=1
        @endslot
        @slot('data') {{ $member->posts }} @endslot
        @slot('title') forum <span class="c-white">post count</span> @endslot
    @endcomponent

    @if ($member->recruiter && $member->recruiter_id !== 0)
        @component('application.components.link-block')
            @slot('link'){{ route('member', $member->recruiter->getUrlParams()) }}@endslot
            @slot('data'){{ $member->recruiter->present()->rankName }}@endslot
            @slot('title') clan <span class="c-white">recruiter</span> @endslot
        @endcomponent
    @endif
</div>

@if ($member->rank_id >= 9)
    <h4>Leadership Info</h4>
    <hr />

    <div class="row">
        @component('application.components.data-block')
            @slot('data') {{ $member->last_trained_at ? $member->last_trained_at->format('Y-m-d') : '--' }} @endslot
            @slot('title') Last <span class="c-white">Rank Training</span> @endslot
        @endcomponent

        @if( $member->trainer)
            @component('application.components.link-block')
                @slot('link'){{ route('member', $member->trainer->getUrlParams()) }}@endslot
                @slot('data') {{$member->trainer->name }} @endslot
                @slot('title') Last <span class="c-white">Trained By</span> @endslot
            @endcomponent
        @endif

        @if($member->xo_at)
            @component('application.components.data-block')
                @slot('data') {{ $member->xo_at ? $member->xo_at->format('Y-m-d') : '--' }} @endslot
                @slot('title') <span class="c-white">XO Since</span> @endslot
            @endcomponent
        @endif

        @if ($member->co_at)
            @component('application.components.data-block')
                @slot('data') {{ $member->co_at ? $member->co_at->format('Y-m-d') : '--' }} @endslot
                @slot('title') <span class="c-white">CO Since</span> @endslot
            @endcomponent
        @endif
    </div>
@endif

