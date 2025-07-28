<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setting extends Model
{
    use HasUuids;
    protected $fillable = [
        'client',
        'logo',
        'primary_color',
        'secondary_color',
        'dark_primary_color',
        'dark_secondary_color',
        'lang',
        'domain_name',
        'duration'
    ];

    public function workshops(): HasMany
    {
        return $this->hasMany(Workshop::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }
}
