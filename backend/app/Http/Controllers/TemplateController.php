<?php

namespace App\Http\Controllers;

use App\Http\Requests\TemplateRequest;
use App\Http\Resources\TemplateResource;
use App\Http\Responses\ApiResponse;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use App\Support\Traits\HandlesExceptions;
use Illuminate\Database\Eloquent\Model;
use App\Models\Template;
use Illuminate\Support\Arr;

class TemplateController extends BaseCrudController
{
    use HandlesExceptions;
    public function __construct(protected TemplateRepositoryInterface $templateRepository)
    {
        parent::__construct();
    }

    protected function getModel(): Model
    {
        return app(Template::class);
    }

    protected function getResourceClass(): string
    {
        return TemplateResource::class;
    }

    protected function getStoreFormRequestClass(): string
    {
        return TemplateRequest::class;
    }

    public function store(): \Illuminate\Http\JsonResponse
    {
        try {
            $request = app($this->getStoreFormRequestClass());
            $attributes = $request->validated();
            $linkableType = Arr::pull($attributes, 'linkable_type', '');
            $linkableId = Arr::pull($attributes, 'linkable_id', '');

            $template = $this->templateRepository
                ->createWithLink($attributes, $linkableType, $linkableId);

            return ApiResponse::success(
                TemplateResource::make($template),
                'Template created successfully.',
                201
            );
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
