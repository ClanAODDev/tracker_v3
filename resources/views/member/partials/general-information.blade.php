<h4>Quick Info</h4>
<hr />

<div class="row">

    @component('application.components.data-block')
        @slot('data') {{ $member->last_activity->diffInDays() }} @endslot

        @slot('title') days since last <span class="c-white">forum activity </span>@endslot
    @endcomponent

    @component('application.components.data-block')
        @slot('data') {{ $member->last_activity->diffInDays() }} @endslot
        @slot('title') days since last <span class="c-white">TS activity </span> @endslot
    @endcomponent

    @component('application.components.data-block')
        @slot('data') {{ $member->join_date }} @endslot
        @slot('title') Member <span class="c-white">join date</span> @endslot
    @endcomponent

    @component('application.components.data-block')
        @slot('data') {{ $member->last_promoted }} @endslot
        @slot('title') Last <span class="c-white">promotion date</span> @endslot
    @endcomponent

    @component('application.components.data-block')
        @slot('data') {{ $member->posts }} @endslot
        @slot('title') forum <span class="c-white">post count</span> @endslot
    @endcomponent

    @if ($member->recruiter && $member->recruiter_id !== 0)
        @component('application.components.data-block')
            @slot('data')
                <a href="{{ route('member', [$member->recruiter_id]) }}">
                    {{ $member->recruiter->present()->rankName }}
                </a>
            @endslot
            @slot('title') clan <span class="c-white">recruiter</span> @endslot
        @endcomponent
    @endif

</div>
