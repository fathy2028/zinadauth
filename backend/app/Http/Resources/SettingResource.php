<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client' => $this->client,
            'logo' => asset($this->logo),
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'dark_primary_color' => $this->dark_primary_color,
            'dark_secondary_color' => $this->dark_secondary_color,
            'lang' => $this->lang,
            'domain_name' => $this->domain_name,
            'duration' => $this->duration,
            'created_at' => $this->created_at
        ];
    }
}
