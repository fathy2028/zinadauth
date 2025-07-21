<?php

namespace App\Enums;

use App\Support\Traits\HasEnumFunctions;

enum UserThemeEnum: string
{
    use HasEnumFunctions;

    case DARK = 'dark';
    case LIGHT = 'light';
}
