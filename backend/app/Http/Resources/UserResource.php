<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'user_name' => $this->user_name,
            'type' => $this->type,
            'theme' => $this->theme,
            'image' => $this->image,
            'web_engine' => $this->web_engine,
            'is_active' => $this->is_active,
            'is_deleted' => $this->is_deleted,
            'last_signed_in' => $this->last_signed_in?->toDateTimeString(),
            'email_verified_at' => $this->email_verified_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            // Include roles if loaded
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            // Include permissions if loaded
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->permissions->pluck('name');
            }),
            // Don't include password or tokens
        ];
    }
}
