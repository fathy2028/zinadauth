<?php

namespace App\Enums;

use App\Support\Traits\HasEnumFunctions;

enum WorkshopStatusTypeEnum: string
{
    use HasEnumFunctions;

    case INACTIVE = 'inactive';
    case ACTIVE = 'active';
    case LOADING = 'loading';
}
