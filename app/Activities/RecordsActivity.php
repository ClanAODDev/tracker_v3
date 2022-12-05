<?php

namespace App\Activities;

use App\Models\Activity;
use ReflectionClass;

/**
 * Trait RecordsActivity.
 */
trait RecordsActivity
{
    /**
     * @param $event
     */
    public function recordActivity($event)
    {
        if (auth()->check()) {
            $actor = auth()->user();

            $this->activity()->create([
                'name' => $this->getActivityName($event),
                'user_id' => $actor->id,
                'subject_id' => $this->id,
                'subject_type' => static::class,
                'division_id' => $actor->member->division_id,
            ]);
        }
    }

    /**
     * @return mixed
     */
    public function activity()
    {
        return $this->morphMany(Activity::class, 'subject')
            ->orderBy('created_at', 'desc');
    }

    public static function feed()
    {
    }

    protected static function bootRecordsActivity()
    {
        foreach (static::getModelEvents() as $event) {
            static::$event(function ($model) use ($event) {
                $model->recordActivity($event);
            });
        }
    }

    /**
     * @return array
     */
    protected static function getModelEvents()
    {
        if (isset(static::$recordEvents)) {
            return static::$recordEvents;
        }

        return [
            'created',
            'deleted',
            'updated',
        ];
    }

    /**
     * @param $action
     * @return string
     */
    protected function getActivityName($action)
    {
        $name = strtolower((new ReflectionClass($this))->getShortName());

        return "{$action}_{$name}";
    }
}
