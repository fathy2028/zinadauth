<?php

namespace App\Models;

use App\Enums\WorkshopStatusTypeEnum;
use App\Support\Traits\HasCreatedBy;
use App\Support\Traits\UseCommonScopes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Workshop extends Model
{
    use HasUuids, HasCreatedBy, UseCommonScopes, HasFactory;

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

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class, 'assignment_workshops')
            ->using(new class extends Pivot {
                use HasUuids;
            })->withPivot([
                'status',
                'assignment_type',
                'qr_status',
                'order_num',
            ]);
    }
}
