<?php

namespace App\Models\Member;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasCustomAttributes
{
    public function AODProfileLink(): Attribute
    {
        return new Attribute(
            fn ($value, $attributes) => 'http://www.clanaod.net/forums/member.php?u=' . $attributes['clan_id']
        );
    }

    public function tsInvalid(): Attribute
    {
        return new Attribute(
            fn ($value, $attributes) => null === carbon_date_or_null_if_zero($attributes['last_ts_activity'])
        );
    }

    public function lastPromoted(): Attribute
    {
        return new Attribute(
            fn ($value, $attributes) => (\strlen($attributes['last_promoted_at']))
                ? Carbon::parse($attributes['last_promoted_at'])->format('Y-m-d')
                : 'Never'
        );
    }

    public function isPending(): Attribute
    {
        return new Attribute(
            fn ($value, $attributes) => $this->memberRequest()->pending()->exists()
        );
    }
}
