<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkshopRequest;
use App\Http\Resources\WorkshopResource;
use App\Models\Workshop;
use App\Repositories\Interfaces\WorkshopRepositoryInterface;
use App\Support\Traits\HandlesFormRequests;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopController extends BaseCrudController
{
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
}
