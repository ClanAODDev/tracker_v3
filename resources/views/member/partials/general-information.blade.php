<h4>General Info</h4>
<hr/>

<div class="row">

    <div class="col-md-12">
        <div class="panel panel-filled">

            <div class="panel-body">

                <div class="row m-t-xs">
                    <div class="col-md-4 col-xs-12 text-center">
                        <h2 class="no-margins" title="{{ $member->join_date?->format('Y-m-d') }}">
                            {{ floor($member->join_date->diffInYears()) }}
                        </h2>
                        {{ str()->plural('Year') }} <span class="c-white">In AOD</span>
                    </div>

                    <div class="col-md-4 col-xs-12 text-center">
                        <h2 class="no-margins">
                            {{ $member->lastPromoted }}
                        </h2>
                        Last <span class="c-white">Promotion Date</span>
                    </div>

                    <div class="col-md-4 col-xs-12 text-center">
                        <h2 class="no-margins">
                            <span title="{{ $member->last_voice_activity }}"
                                  class="{{  getMemberProfileActivityClass($member->last_voice_activity) }}">
                                {{ $member->present()->lastActive('last_voice_activity', skipUnits: ['weeks','months']) }}</span>
                        </h2>
                        Since Last <span class="c-white">Discord Voice Activity</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>


<div class="row">
    @component('application.components.link-block')
        @slot('link')
            https://www.clanaod.net/forums/search.php?do=finduser&amp;userid={{ $member->clan_id }}&amp;
            contenttype=vBForum_Post&amp;showposts=1
        @endslot
        @slot('data')
            {{ $member->posts }}
        @endslot
        @slot('title')
            forum <span class="c-white">post count</span>
        @endslot
    @endcomponent

    @if ($member->recruiter && $member->recruiter_id !== 0)
        @component('application.components.link-block')
            @slot('link')
                {{ route('member', $member->recruiter->getUrlParams()) }}
            @endslot
            @slot('data')
                {{ $member->recruiter->present()->rankName }}
            @endslot
            @slot('title')
                clan <span class="c-white">recruiter</span>
            @endslot
        @endcomponent
    @endif


    @if ($member->rank->value >= \App\Enums\Rank::SERGEANT->value)
        @component('application.components.data-block')
            @slot('data')
                {{ $member->last_trained_at ? $member->last_trained_at->format('Y-m-d') : '--' }}
            @endslot
            @slot('title')
                Last <span class="c-white">Rank Training</span>
            @endslot
        @endcomponent

        @if( $member->trainer)
            @component('application.components.link-block')
                @slot('link')
                    {{ route('member', $member->trainer->getUrlParams()) }}
                @endslot
                @slot('data')
                    {{$member->trainer->name }}
                @endslot
                @slot('title')
                    Last <span class="c-white">Trained By</span>
                @endslot
            @endcomponent
        @endif
    @endif

</div>