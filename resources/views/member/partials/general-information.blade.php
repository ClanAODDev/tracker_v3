<h4>Quick Info</h4>
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
         @slot('data') {!! $member->last_ts_activity !!} @endslot
         @slot('title') last <span class="c-white">TS activity </span> @endslot
     @endcomponent

    @component('application.components.data-block')
        @slot('data') {{ $member->join_date }} @endslot
        @slot('title') Member <span class="c-white">join date</span> @endslot
    @endcomponent

    @component('application.components.data-block')
        @slot('data') {{ $member->last_promoted }} @endslot
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
            @slot('link'){{ route('member', [$member->recruiter_id]) }}@endslot
            @slot('data'){{ $member->recruiter->present()->rankName }}@endslot
            @slot('title') clan <span class="c-white">recruiter</span> @endslot
        @endcomponent
    @endif

</div>
