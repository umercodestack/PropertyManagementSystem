<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'surname' => $this->surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'country' => $this->country,
            'city' => $this->city,
            'ota_name' => 'livedin',
            'cnic_passport' => $this->cnic_passport,
            'adult' => $this->adult,
            'children' => $this->children,
            'rooms' => $this->rooms,
            'custom_discount' => $this->custom_discount,
            'payment_method' => $this->payment_method,
            'guest_id' => $this->guest_id,
            'host_id' => $this->host_id,
            'listing_id' => $this->listing_id,
            'booking_sources' => $this->booking_sources,
            'booking_date_start' => $this->booking_date_start,
            'booking_date_end' => $this->booking_date_end,
            'service_fee' => $this->service_fee,
            'cleaning_fee' => $this->cleaning_fee,
            'per_night_price' => $this->per_night_price,
            'total_price' => $this->total_price,
            'total_nights' => $this->total_nights ?? null,
            "booking_type" => $this->booking_type,
        ];
    }
}
