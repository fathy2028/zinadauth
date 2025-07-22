<?php

namespace App\Models;

use App\Enums\WorkshopStatusTypeEnum;
use App\Support\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Workshop extends Model
{
    use HasUuids, HasCreatedBy;

    protected $fillable = [
        'title',
        'description',
        'start_at',
        'end_at',
        'created_by',
        'setting_id',
        'is_deleted',
        'qr_status',
        'status',
        'pin_code',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'is_deleted' => 'boolean',
        'qr_status' => 'boolean',
        'status' => WorkshopStatusTypeEnum::class,
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_workshops')
            ->using(new class extends Pivot {
                use HasUuids;
            })->withPivot(['status']);
    }

    #[Scope]
    protected function status(Builder $query, \StringBackedEnum $status): void
    {
        $query->where('status', $status);
    }

    #[Scope]
    protected function isDeleted(Builder $query): void
    {
        $query->where('is_deleted', true);
    }
}
