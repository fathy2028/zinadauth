<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingsRequest;
use App\Http\Requests\WorkshopRequest;
use App\Http\Resources\SettingResource;
use App\Http\Resources\WorkshopResource;
use App\Http\Responses\ApiResponse;
use App\Models\Workshop;
use App\Repositories\Eloquent\SettingRepository;
use App\Repositories\Interfaces\SettingRepositoryInterface;
use App\Repositories\Interfaces\WorkshopRepositoryInterface;
use App\Support\Traits\UploadsFiles;
use Illuminate\Database\Eloquent\Model;

class WorkshopController extends BaseCrudController
{
    public function __construct(
        protected WorkshopRepositoryInterface $workshopRepository,
        protected SettingRepositoryInterface $settingRepository
    )
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

            $setting = $this->settingRepository->createWithLogo($request->validated(), $request->logo);
            $this->workshopRepository->assignSetting($workshop, $setting);

            return ApiResponse::success(
                SettingResource::make($setting),
                'Setting created successfully.'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'create setting for workshop');
        }
    }
}
