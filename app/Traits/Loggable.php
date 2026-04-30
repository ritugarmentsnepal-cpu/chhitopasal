<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait Loggable
{
    /**
     * ARCH-02: Static flag to suppress logging during bulk operations.
     * Set Loggable::$suppressLogging = true before bulk ops, false after.
     */
    public static bool $suppressLogging = false;

    protected static function bootLoggable()
    {
        static::created(function ($model) {
            if (static::$suppressLogging) return;
            $model->logActivity('created', ['attributes' => $model->getAttributes()]);
        });

        static::updated(function ($model) {
            if (static::$suppressLogging) return;
            $changes = $model->getChanges();
            $original = array_intersect_key($model->getOriginal(), $changes);
            
            $model->logActivity('updated', [
                'old' => $original,
                'new' => $changes
            ]);
        });

        static::deleted(function ($model) {
            if (static::$suppressLogging) return;
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
