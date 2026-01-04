<?php

namespace Modules\Otp\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerificationCodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $debugMode = config('app.debug');
        return [
            'code' => $debugMode ? $this->code : '********',
            'contact_type' => $this->contact_type,
            'contact' => $this->contact,
            'expires_at' => $this->expires_at,
        ];
    }
}
