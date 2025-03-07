@if($divisionAnniversaries->count())
    <h3 class="m-b-xs text-uppercase m-t-xxxl" id="platoons">
        {{ now()->monthName }} <span class="text-muted">Anniversaries</span>
        <hr/>
    </h3>

    @foreach($divisionAnniversaries as $anniversary)
        <a href="{{ route('member', [$anniversary->clan_id, $anniversary->name]) }}#achievements"
           class="btn btn-default" style="margin-bottom:15px;" target="_blank"
        >

            @php
                $iconClass = 'fas fa-trophy';

                if ($anniversary->years_since_joined >= 20) {
                    $iconClass .= ' fa-lg';
                    $iconColor = '#E5E4E2';
                    $iconTitle = '20+ Years';
                } elseif ($anniversary->years_since_joined >= 15) {
                    $iconClass .= ' fa-md';
                    $iconColor = '#D4AF37';
                    $iconTitle = '15+ Years';
                } elseif ($anniversary->years_since_joined >= 10) {
                    $iconClass .= ' fa-sm';
                    $iconColor = '#C0C0C0';
                    $iconTitle = '10+ Years';
                } elseif ($anniversary->years_since_joined >= 5){
                    $iconClass .= ' fa-sm';
                    $iconColor = '#cd7f32';
                    $iconTitle = '5+ Years';
                }
            @endphp

            @if ($anniversary->years_since_joined >= 5)
                <i class="{{ $iconClass }}" style="color: {{ $iconColor }};" title="{{ $iconTitle }}"></i>
            @endif

            {{ $anniversary->name }}
            <span class="label label-success text-uppercase" style="color:#000;">
                {{ $anniversary->years_since_joined }} {{ str('yr')->plural($anniversary->years_since_joined) }}
            </span>
        </a>
    @endforeach
@endif
