<h1><img src="https://clanaod.net/v3tracker/images/logo_v2.png" /> AOD Tracker</h1>
<h2>External Recruitment Notice</h2>
<p>{{ $recruiter->name}} from {{ $recruiter->division->name }} recently recruited a member into your division, so we thought we'd let you know the details so you can follow up.</p>
<p>
    <strong>New recruit information</strong>
    {{ $recruit->name }} ({{ $recruit->clan_id }}) - <a
            href="{{ route('member', $recruit->clan_id) }}">View profile on tracker</a>
</p>
