<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'start_at' => $this->start_at->format('Y-m-d'),
            'end_at' => $this->end_at->format('Y-m-d'),
            'status' => $this->status,
            'qr_status' => $this->qr_status,
            'pin_code' => $this->pin_code,
            'setting_id' => $this->setting_id,
            'created_by' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name
                ],
            'created_at' => $this->created_at
        ];
    }
}
