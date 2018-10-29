@component('mail::message')
![AOD Logo][logo]
# Hello, {{ $user->name }}!

We’re writing to **confirm your interest** in attending the fall 2019 AOD Live meetup currently planned to take place in Las Vegas, Nevada.

We’re excited to be able to make this event possible and look forward to sharing more information as it is available. By opting into the AOD Live event you will be included in future communication containing more schedule and logistics information.

We have not confirmed specific dates or venues at this time, so the most you can plan for is the September through November timeframe in 2019. At this point we are solely gauging interest and building a distribution list for those seriously planning on going.

Please keep any details regarding the meetup to yourself and family members who may go with you. Please do not assume other members have event information. While it may seem odd, trust that safety and the integrity of the event is paramount, and history shows the best way to keep everything cozy is to share the most specific information with those who will be present.

If at any time you decide you cannot make it, please use the Opt Out button below to update your response or visit [https://tracker.clanaod.net/vegas2019](https://tracker.clanaod.net/vegas2019).

For questions, contact [AOD_Silencer77] via forum message.

We can’t wait hang out in person, so start saving now!

@component('mail::button', ['url' => route('vegas-survey')])
Opt Out
@endcomponent

Thanks,<br>
Clan AOD Leadership

[logo]: {{ asset('images/aod-logo-modern.png') }} "Logo"
[AOD_Silencer77]: {!! doForumFunction(['20755'], 'pm') !!}
@endcomponent



