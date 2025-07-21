<?php

namespace App\Enums;

use App\Support\Traits\HasEnumFunctions;

enum AttendanceTypeEnum: string
{
    use HasEnumFunctions;

    case NOT_EXIST = 'not_exist';
    case NOT_ATTEND = 'not_attend';
    case ATTEND = 'attend';
}
