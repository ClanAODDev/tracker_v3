@component('mail::message')

## {{ $fireteam->name }}

The fireteam leader for your **{{ ucwords($fireteam->type) }}** fireteam is confirmed! Here are the members of your party!

* {{ $fireteam->owner->name }} &#x2726;{{ $fireteam->owner_light }}
@foreach ($fireteam->players as $player)
    * {{ $player->name }} &#x2726;{{ $player->pivot->light }}
@endforeach

Your fireteam leader should have already contacted you with details regarding this fireteam. Good luck!
@endcomponent