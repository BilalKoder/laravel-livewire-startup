<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Progress_listResource extends JsonResource
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
            'id' => $this->id,
            'remarks' => $this->remarks,
            'user' => new UserResource($this->whenLoaded('user')),
            'task' => new TaskResource($this->whenLoaded('task')),
        ];
    }
}
