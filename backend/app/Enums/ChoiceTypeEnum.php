<?php

namespace App\Enums;

use App\Support\Traits\HasEnumFunctions;

enum ChoiceTypeEnum: string
{
    use HasEnumFunctions;

    case STANDARD = 'standard';
    case CUSTOM = 'custom';
}
