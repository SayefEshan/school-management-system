<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,            
            // Profile data
            'first_name' => $this->first_name ?? '',
            'last_name' => $this->last_name ?? '',
            'gender' => $this->gender ?? '',
            'image' => $this->image,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_active' => $this->is_active,
            'provider' => $this->provider,
            'provider_id' => $this->provider_id,
            // Roles
            'roles' => $this->roles->pluck('name'),
            // Timestamps
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
   }
}
