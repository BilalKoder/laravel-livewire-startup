<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAssignedTask extends Model
{
    use HasFactory;

    protected $table = 'user_assigned_tasks';

    public function task(){
        return $this->belongsTo(UserTask::class, 'task_id', 'id');
    }

}
