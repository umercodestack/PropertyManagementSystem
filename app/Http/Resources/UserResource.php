<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $host_type_id
 * @property mixed $name
 * @property mixed $surname
 * @property mixed $email
 * @property mixed $email_verification_code
 * @property mixed $phone
 * @property mixed $dob
 * @property mixed $gender
 * @property mixed $country
 * @property mixed $email_verified
 * @property mixed $access_token
 */
class UserResource extends JsonResource
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
            'host_type_id' => $this->host_type_id,
            'name' => $this->name,
            'surname' => $this->surname,
            'email' => $this->email,
            'email_verified' => $this->email_verified,
            'email_verification_code' => $this->email_verification_code,
            'phone' => $this->phone,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'country' => $this->country,
            'city' => $this->city,
            'emergency' => $this->emergency,
            'plan_verified' => $this->plan_verified,
            'access_token' => $this->access_token,
            'is_block' => $this->is_block
        ];
    }
}
 