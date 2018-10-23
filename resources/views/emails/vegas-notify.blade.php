@component('mail::message')
![AOD Logo][logo]
# Hello, {{ auth()->user()->name }}!

Just wanted to **confirm your interest** in attending the AOD US Fall 2019 Meetup currently planned to take place in Las Vegas, NV.

We're excited to be able to make this event possible. However, we have not solidified exactly what the timeline will look like, nor have we agreed on any logistics. At this point, we are solely gauging interest before we move forward.

Please keep any details regarding the meetup to yourself, and do not discuss specifics with other members. If you decide you would like to **not** be considered as a potential attendee, you can opt out of future notifications via the button below.

@component('mail::button', ['url' => route('vegas-survey')])
Opt Out
@endcomponent

Thanks,<br>
Clan AOD Leadership

[logo]: {{asset('images/aod-logo-modern.png')}} "Logo"
@endcomponent



