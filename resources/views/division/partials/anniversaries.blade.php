@if($divisionAnniversaries->count())
    <h3 class="m-b-xs text-uppercase m-t-xxxl" id="platoons">
        {{ now()->monthName }} Anniversaries
        <hr/>
    </h3>

    @foreach($divisionAnniversaries as $anniversary)
        <a href="{{ doForumFunction([$anniversary->clan_id], 'forumProfile') }}"
           class="btn btn-success" style="margin-bottom:15px;" target="_blank"
        >
            <strong>{{ $anniversary->name }}</strong>
            - {{ $anniversary->years_since_joined }} {{ Str::plural('year', $anniversary->years_since_joined) }}
        </a>
    @endforeach
@endif