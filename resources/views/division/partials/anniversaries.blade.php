@if($divisionAnniversaries->count())
    <h3 class="m-b-xs text-uppercase m-t-xxxl" id="platoons">
        {{ now()->monthName }} <span class="text-muted">Anniversaries</span>
        <hr/>
    </h3>

    @foreach($divisionAnniversaries as $anniversary)
        <a href="{{ doForumFunction([$anniversary->clan_id], 'forumProfile') }}&tab=myawards#myawards"
           class="btn btn-default" style="margin-bottom:15px;" target="_blank"
        >
            
        @php
            
            $iconClass = 'fas fa-trophy';
            $iconColor = '#cd7f32';
            $iconTitle = '5+ Years';
        
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
            }
            
        @endphp

            <i class="{{ $iconClass }}" style="color: {{ $iconColor }};" title="{{ $iconTitle }}"></i>

            {{ $anniversary->name }}
            <span class="label label-success text-uppercase" style="color:#000;">
                {{ $anniversary->years_since_joined }} {{ str('yr')->plural($anniversary->years_since_joined) }}
            </span>
        </a>
    @endforeach
@endif
