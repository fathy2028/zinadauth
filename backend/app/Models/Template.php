<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasUuids;
    protected $fillable = [
        'title',
        'title_ar',
        'description',
        'description_ar',
        'setting_id'
    ];

    public function setting(): BelongsTo
    {
        return $this->belongsTo(Setting::class);
    }

    public function templateLinks(): HasMany
    {
        return $this->hasMany(TemplateLink::class);
    }
}
