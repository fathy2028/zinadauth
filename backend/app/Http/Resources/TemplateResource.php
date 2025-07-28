<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        return [
            'id' => $this->id,
            'title' => $locale === 'en' ? $this->title : $this->title_ar,
            'description' => $locale === 'en' ? $this->description : $this->description_ar,
            'setting_id' => $this->setting_id,
            'linkable' => $this->templateLinks->first()->linkable ?? null,
            'created_at' => $this->created_at,
        ];
    }
}
