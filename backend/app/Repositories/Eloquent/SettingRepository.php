<?php

namespace App\Repositories\Eloquent;

use App\Models\Setting;
use App\Models\Workshop;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Support\Traits\UploadsFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    use UploadsFiles;
    public function __construct(Setting $setting)
    {
        parent::__construct($setting);
    }

    public function createWithLogo(array $attributes, UploadedFile $logo)
    {
        $attributes['logo'] = $this->storeFile('public', 'workshop/settings/logo', $logo);

        return $this->model->create($attributes);
    }
}
