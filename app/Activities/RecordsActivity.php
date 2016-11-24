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

            Activity::create([
                'subject_id' => $this->id,
                'subject_type' => get_class($this),
                'name' => $this->getActivityName($this, $event),
                'user_id' => $user->id,
                'division_id' => $user->member->primaryDivision->id
            ]);

            // @TODO: Add slack hook for notifications
            // use activity name?
        }
    }

    protected function getActivityName($model, $action)
    {
        $name = strtolower((new \ReflectionClass($model))->getShortName());

        return "{$action}_{$name}";
    }
}
