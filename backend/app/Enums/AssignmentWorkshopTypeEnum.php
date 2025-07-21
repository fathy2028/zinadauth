<?php

namespace App\Enums;

use App\Support\Traits\HasEnumFunctions;

enum AssignmentWorkshopTypeEnum: string
{
    use HasEnumFunctions;

    case INTERACTIVE = 'interactive';
    case TRADITIONAL = 'traditional';
}
