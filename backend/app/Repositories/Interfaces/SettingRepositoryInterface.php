<?php

namespace App\Repositories\Interfaces;

use App\Models\Setting;
use App\Models\Workshop;
use Illuminate\Http\UploadedFile;

interface SettingRepositoryInterface extends BaseRepositoryInterface
{
    public function createWithLogo(array $attributes, UploadedFile $logo);
}
