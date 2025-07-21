<?php

namespace App\Enums;

use App\Support\Traits\HasEnumFunctions;

enum AssignmentQuestionOrderEnum: string
{
    use HasEnumFunctions;

    case ORDERED = 'ordered';
    case RANDOM = 'random';
}
