<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApartmentPriceResource extends JsonResource
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
            'apartment' => $this->apartment,
            'discount' => $this->discount,
            'price' => $this->price,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ];
    }
}
 