<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'task_id' => $this->id,
            'task_title' => $this->title,
            'task_content' => $this->content,
            'task_type' => $this->task_type,
            'task_meta' => $this->meta_data,
            'updated_at' => Carbon::parse($this->updated_at)->format('d/m/Y'),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            /*'videos' => VideoResource::collection($this->whenLoaded('videos')),*/
            'progress_lists' => Progress_listResource::collection($this->whenLoaded('progress_lists')),
            'user' => new UserResource($this->whenLoaded('user')),
            /*'category' => new CategoryResource($this->whenLoaded('category')),*/
        ];
    }
}
