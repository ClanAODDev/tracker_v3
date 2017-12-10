@component('mail::message')

Your **{{ ucwords($fireteam->type) }}** fireteam is now full! Here are the members of your party!

* {{ $fireteam->owner->name }} &#x2726;{{ $fireteam->owner_light }}
@foreach ($fireteam->players as $player)
* {{ $player->name }} &#x2726;{{ $player->pivot->light }}
@endforeach

Use the button below to contact your fireteam via the Clan AOD forums.

@component('mail::button', ['url' => doForumFunction($fireteam->players->pluck('clan_id')->toArray(), 'pm')])
Contact Fireteam
@endcomponent

Eyes up, Guardian!
@endcomponent