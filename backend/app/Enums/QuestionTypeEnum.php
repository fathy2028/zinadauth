<?php

namespace App\Enums;

use App\Support\Traits\HasEnumFunctions;

enum QuestionTypeEnum: string
{
    use HasEnumFunctions;

    case SINGLE_CHOICE = 'single_choice';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case TEXT = 'text';
    case CODE = 'code';
}
