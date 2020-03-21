@component('mail::message')

You recently created a **{{ ucwords($fireteam->type) }}** Fireteam with {{ $fireteam->players_needed }} {{ Str::plural('spot', $fireteam->players_needed) }}! You, as well as all of your fireteam members, will be notified once all available slots are filled. To return to the Fireteam page, or to manage your fireteam, use the button below.

Here are a few tips for ensuring your fireteam goes off without a hitch!

* Communicate! Let your fireteam know when to be on teamspeak, and give ample notice!
* Research! A prepared fireteam is always better than the opposite.
* Take charge! As the fireteam leader, be clear about your expectations.
* and lastly... Have fun! After all, that's what we're here for.

@component('mail::button', ['url' => route('fireteams.index')])
View Fireteams
@endcomponent

Eyes up, Guardian!
@endcomponent