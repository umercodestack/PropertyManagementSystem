<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentResource extends JsonResource
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
            'user' => $this->user,
            'apartment_type' => $this->apartment_type,
            'rental_type' => $this->rental_type,
            'address' => $this->address,
            'description' => $this->description,
            'title' => $this->title,
            'max_guests' => $this->max_guests,
            'bedrooms' => $this->bedrooms,
            'beds' => $this->beds,
            'bathrooms' => $this->bathrooms,
            'amenities' => $this->amenities,
            'images' => $this->images,
            'channex_json' => $this->channex_json,
        ];
    }
}
 