@if($divisionAnniversaries->count())
    <h3 class="m-b-xs text-uppercase m-t-xxxl" id="platoons">
        {{ now()->monthName }} Anniversaries
        <hr/>
    </h3>

    @foreach($divisionAnniversaries as $anniversary)
        <a href="{{ doForumFunction([$anniversary->clan_id], 'forumProfile') }}&tab=myawards#myawards"
           class="btn btn-default" style="margin-bottom:15px;" target="_blank"
        >
            @if($anniversary->years_since_joined >= 5)
                <i class="fas fa-trophy fa-sm" style="color: #cd7f32;" title="5 Years"></i>
            @elseif($anniversary->years_since_joined >= 10)
                <i class="fas fa-trophy fa-sm" style="color: #C0C0C0;" title="10 Years"></i>
            @elseif($anniversary->years_since_joined >= 15)
                <i class="fas fa-trophy fa-md" style="color: #D4AF37;" title="15 Years"></i>
            @elseif($anniversary->years_since_joined >= 20)
                <i class="fas fa-trophy fa-lg" title="20 Years" style="color: #E5E4E2;"></i>
            @endif

            {{ $anniversary->name }}
            <span class="label label-success text-uppercase" style="color:#000;">
                {{ $anniversary->years_since_joined }} {{ Str::plural('yr', $anniversary->years_since_joined) }}
            </span>
        </a>
    @endforeach
@endif