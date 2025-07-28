<?php

namespace App\Repositories\Eloquent;

use App\Models\Setting;
use App\Models\Workshop;
use App\Repositories\Interfaces\WorkshopRepositoryInterface;
use Illuminate\Support\Collection;

class WorkshopRepository extends BaseRepository implements WorkshopRepositoryInterface
{
    public function __construct(Workshop $workshop)
    {
        parent::__construct($workshop);
    }

    public function assignSetting(Workshop $workshop, Setting $setting): void
    {
        $workshop->update(['setting_id' => $setting->id]);
    }
}
