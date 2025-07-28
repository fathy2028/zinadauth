<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingsRequest;
use App\Http\Requests\WorkshopRequest;
use App\Http\Resources\SettingResource;
use App\Http\Resources\WorkshopResource;
use App\Http\Responses\ApiResponse;
use App\Models\Setting;
use App\Models\Workshop;
use App\Repositories\Interfaces\WorkshopRepositoryInterface;
use App\Support\Traits\HandlesFormRequests;
use App\Support\Traits\UploadsFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class WorkshopController extends BaseCrudController
{
    use UploadsFiles;
    public function __construct(protected WorkshopRepositoryInterface $workshopRepository)
    {
        parent::__construct();
    }

    protected function getModel(): Model
    {
        return app(Workshop::class);
    }

    protected function getResourceClass(): string
    {
        return WorkshopResource::class;
    }

    protected function getStoreFormRequestClass(): string
    {
        return WorkshopRequest::class;
    }

    public function storeSettings(SettingsRequest $request, Workshop $workshop)
    {
        try {
            $this->authorize('edit', $workshop);

            $data = $request->validated();
            if (isset($request->logo)) {
                $data['logo'] = $this->storeFile('public', 'workshop/settings/logo', $request->logo);
            }

            $setting = $workshop->setting()->create($data);

            return ApiResponse::success(
                SettingResource::make($setting),
                'Setting created successfully.'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'create setting for workshop');
        }
    }
}
