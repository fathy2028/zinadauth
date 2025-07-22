<?php

namespace App\Models;

use App\Enums\AssignmentQuestionOrderEnum;
use App\Traits\HasCreatedBy;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasUuid, HasCreatedBy;

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
