<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTask extends Model
{
    use HasFactory;

     protected $fillable = [
        'title',
        'description',
        'image',
        'goal',
        'type',
        'meta_data',
        'category_id',
        'user_id',
    ];



    public function progress(){
        return $this->hasMany(Progress::class, 'task_id','id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }
}
