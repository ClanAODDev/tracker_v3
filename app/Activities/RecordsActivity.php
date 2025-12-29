<?php

namespace App\Activities;

use App\Enums\ActivityType;
use App\Models\Activity;

trait RecordsActivity
{
    public function recordActivity(ActivityType $type, array $properties = [])
    {
        if (auth()->check()) {
            $actor = auth()->user();

            $this->activity()->create([
                'name' => $type,
                'user_id' => $actor->id,
                'subject_id' => $this->id,
                'subject_type' => static::class,
                'division_id' => $actor->member?->division_id,
                'properties' => $properties ?: null,
            ]);
        }
    }

    public function activity()
    {
        return $this->morphMany(Activity::class, 'subject')
            ->orderBy('created_at', 'desc');
    }
}
