<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress_list extends Model
{
    use HasFactory;

    protected $fillable = [
        'remarks',
        'user_id',
        'task_id',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function task(){
        return $this->belongsTo(Task::class);
    }
}
