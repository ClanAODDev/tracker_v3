@if($divisionAnniversaries->count())
    <h3 class="m-b-xs text-uppercase m-t-xxxl">
        {{ now()->monthName }} <span class="text-muted">Milestones</span>
        <hr/>
    </h3>

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
@endif
