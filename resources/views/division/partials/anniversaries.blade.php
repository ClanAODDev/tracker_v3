@if($divisionAnniversaries->count())
    <h3 class="m-b-xs text-uppercase m-t-xxxl" id="platoons">
        {{ now()->monthName }} Anniversaries
        <hr/>
    </h3>

    @foreach($divisionAnniversaries as $anniversary)
        <a href="{{ doForumFunction([$anniversary->clan_id], 'forumProfile') }}&tab=myawards#myawards"
           class="btn btn-default" style="margin-bottom:15px;" target="_blank"
        >
            {{ $anniversary->name }}
            <span class="label label-success text-uppercase" style="color:#000;">
                {{ $anniversary->years_since_joined }} {{ Str::plural('yr', $anniversary->years_since_joined) }}
            </span>
        </a>
    @endforeach
@endif