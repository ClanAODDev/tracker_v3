<?php

namespace App\Activities;

use App\Activity;

trait RecordsActivity
{
    protected static function bootRecordsActivity()
    {
        foreach (static::getModelEvents() as $event) {
            static::$event(function ($model) use ($event) {
                $model->recordActivity($event);
            });
        }
    }

    protected static function getModelEvents()
    {
        if (isset(static::$recordEvents)) {
            return static::$recordEvents;
        }

        return [
            'created',
            'deleted',
            'updated'
        ];
    }

    public function recordActivity($event) //$post->recordActivity('favorited')
    {
        if (\Auth::check()) {
            $user = \Auth::user();

            $this->activity()->create([
                'name' => $this->getActivityName($event),
                'user_id' => $user->id,
                'division_id' => $user->member->primaryDivision->id
            ]);

            // @TODO: Add slack hook for notifications
            // use activity name?
        }
    }

    public function activity()
    {
        return $this->morphMany(Activity::class, 'subject');
    }

    protected function getActivityName($action)
    {
        $name = strtolower((new \ReflectionClass($this))->getShortName());

        return "{$action}_{$name}";
    }

    public static function feed()
    {

    }
}
