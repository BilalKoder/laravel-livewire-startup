<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VideoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'video_id' => $this->id,
            'title' => $this->title,
            'task_id' => $this->task_id,
            'url' => $this->url
        ];
    }
}
