<?php

namespace App\Models;

use App\Enums\AssignmentQuestionOrderEnum;
use App\Support\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Assignment extends Model
{
    use HasUuids, HasCreatedBy;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'question_order',
    ];

    protected $casts = [
        'question_order' => AssignmentQuestionOrderEnum::class,
    ];

    public function workshops(): BelongsToMany
    {
        return $this->belongsToMany(Workshop::class, 'assignment_workshops')
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
