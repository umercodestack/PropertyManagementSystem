<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $notificationDetail = json_decode($this->notification_detail, true);

        return [
            'id' => $this->id,
            'notification_detail' => $this->notification_detail,
            'user_id' => $this->user,
            'is_checked' => $this->is_checked,
            'message' => $notificationDetail['payload']['message'] ?? null, 
        ];
    }
}
 