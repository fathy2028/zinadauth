<?php

namespace App\Repositories\Interfaces;

use App\Models\Setting;
use App\Models\Workshop;

interface WorkshopRepositoryInterface extends BaseRepositoryInterface
{
    public function assignSetting(Workshop $workshop, Setting $setting);
}
