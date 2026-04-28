<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait Loggable
{
    protected static function bootLoggable()
    {
        static::created(function ($model) {
            $model->logActivity('created', ['attributes' => $model->getAttributes()]);
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = array_intersect_key($model->getOriginal(), $changes);
            
            $model->logActivity('updated', [
                'old' => $original,
                'new' => $changes
            ]);
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', ['attributes' => $model->getAttributes()]);
        });
    }

    public function logActivity($action, $details = [])
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'details' => $details
        ]);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'model');
    }
}
