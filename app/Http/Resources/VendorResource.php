<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorResource extends JsonResource
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
            'name' => $this->name,
            'location' => $this->location,
            'occupation' => $this->occupation,
            'availability' => $this->availability,
            'last_hired' => $this->last_hired,
            'time_duration' => $this->time_duration,
            'picture' => $this->picture,
            'is_active' => $this->is_active,
            'phone' => $this->phone,
            'country_code' => $this->country_code,
            'country_short_name' => $this->country_short_name,
            'service_id' => $this->service->id ?? null,
            'service_name' => $this->service->service_name ?? null
        ];
    }
}
 