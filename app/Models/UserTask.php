<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTask extends Model
{
    use HasFactory;


    public function progress(){
        return $this->hasMany(Progress::class, 'task_id','id');
    }
}
