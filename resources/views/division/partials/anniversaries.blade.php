@if($divisionAnniversaries->count())
<div class="division-section animate-fade-in-up" style="animation-delay: 0.3s">
    <h3 class="division-section-title">
        {{ now()->monthName }} <span class="text-muted">Milestones</span>
    </h3>
    <hr/>

    <div class="milestone-grid">
        @foreach($divisionAnniversaries as $anniversary)
            @php $trophy = getAnniversaryTrophy($anniversary->years_since_joined); @endphp
            <a href="{{ route('member', [$anniversary->clan_id, $anniversary->name]) }}"
               class="milestone-chip {{ $trophy ? 'milestone-chip--featured' : '' }}">
                <div class="milestone-icon">
                    @if($trophy)
                        <i class="{{ $trophy['class'] }}" style="color: {{ $trophy['color'] }}"></i>
                    @else
                        <i class="fa fa-star"></i>
                    @endif
                </div>
                <div class="milestone-info">
                    <span class="milestone-name">{{ $anniversary->rank?->getAbbreviation() }} {{ $anniversary->name }}</span>
                    <span class="milestone-years">{{ $anniversary->years_since_joined }} {{ str('yr')->plural($anniversary->years_since_joined) }}</span>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endif
