<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TemplateLink extends Model
{
    use HasUuids;
    protected $fillable = [
        'template_id',
        'linkable_type',
        'linkable_id',
    ];
    public $timestamps = false;

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }
}
