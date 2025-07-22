<?php

namespace App\Support\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait HasCreatedBy
{
    /**
     * Boot the trait.
     */
    protected static function bootHasCreatedBy()
    {
        static::creating(function ($model) {
            // Set created_by if authenticated and field exists
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
