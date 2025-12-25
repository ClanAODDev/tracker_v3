@if($divisionAnniversaries->count())
<div class="division-section animate-fade-in-up" style="animation-delay: 0.3s">
    <h3 class="division-section-title">
        {{ now()->monthName }} <span class="text-muted">Milestones</span>
    </h3>
    <hr/>

    @foreach($divisionAnniversaries as $anniversary)
        @php $trophy = getAnniversaryTrophy($anniversary->years_since_joined); @endphp
        <a href="{{ route('member', [$anniversary->clan_id, $anniversary->name]) }}"
           class="btn btn-default anniversary-btn">
            @if($trophy)
                <i class="{{ $trophy['class'] }}" style="color: {{ $trophy['color'] }};"></i>
            @endif
            {{ $anniversary->rank?->getAbbreviation() }} {{ $anniversary->name }}
            <span class="label label-default">
                {{ $anniversary->years_since_joined }} {{ str('yr')->plural($anniversary->years_since_joined) }}
            </span>
        </a>
    @endforeach
</div>
@endif
