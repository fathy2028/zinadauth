<?php

namespace App\Enums;

use App\Support\Traits\HasEnumFunctions;

enum UserTypeEnum: string
{
    use HasEnumFunctions;

    case ADMIN = 'admin';
    case PARTICIPANT = 'participant';
    case FACILITATOR = 'facilitator';
}
