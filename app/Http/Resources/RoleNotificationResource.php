<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleNotificationResource extends JsonResource
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
            'module_id' => $this->module_id,
            'title' => $this->title ?? null,
            'source' => $this->source ?? null,
            'message' => $this->message ?? null,
            'status' => $this->status ?? null,
            'url' => $this->url ?? null,
            'is_seen_by_all' => $this->is_seen_by_all,
            'created_at' => $this->created_at,
        ];
    }
}
