<?php

namespace App\Models;

use App\Enums\AssignmentQuestionOrderEnum;
use App\Support\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

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
}
