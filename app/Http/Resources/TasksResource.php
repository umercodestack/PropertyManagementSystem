<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TasksResource extends JsonResource
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
            'task_title' => $this->task_title,
            'category' => $this->category,
            'vendor' => $this->vendor,
            'apartment' => $this->apartment,
            'stage' => $this->stage,
            'frequency' => $this->frequency,
            'picture' => $this->picture,
            'time_duration' => $this->time_duration,
            'date_duration' => $this->date_duration,
            'completion_time' => $this->completion_time,
            'status' => $this->status,
        ];
    }

    public function with($request): array
    {
        return [
            'status'=>'success'
        ];
    }
}
 