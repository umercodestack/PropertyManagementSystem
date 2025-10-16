<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AirbnbListingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $details = $this->details ? json_decode($this->details, true) : null;
        if(!empty($details['amenities']))
        {
            $details['amenities'] = array_map('strtolower', array_keys($details['amenities']));
        }
        
        $details['name'] = $this->name;

        $bookingSetting = $this->booking_setting ? json_decode($this->booking_setting, true) : null;
        $bookingSettingData = [];

        if(!empty($bookingSetting)) {
            if(!empty($bookingSetting['cancellation_policy_settings']['cancellation_policy_category'])) {
                $bookingSettingData['cancellation_policy_category'] = $bookingSetting['cancellation_policy_settings']['cancellation_policy_category'];
            }
            if(!empty($bookingSetting['instant_booking_allowed_category'])) {
                $bookingSettingData['instant_booking_allowed_category'] = $bookingSetting['instant_booking_allowed_category'];
            }
        }
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'listing_id' => $this->listing_id,
            'details' => $details,
            'prices' => $this->prices ? json_decode($this->prices, true) : null,
            'description' => $this->description ? json_decode($this->description, true) : null,
            'images' => $this->images,
            'rooms' => $this->rooms,
            'booking_setting' => $bookingSettingData,
            'created_at' => $this->created_at
        ];
    }
}
