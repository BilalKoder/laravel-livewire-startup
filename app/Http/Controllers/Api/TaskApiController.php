<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Progress_listResource;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    public function index()
    {
        $tasks = Task::with(['user', 'images', 'progress_lists'])->paginate();
        return TaskResource::collection($tasks);
    }

    public function show($id)
    {
        $task = Task::with([
            'user', 'images', 'progress_lists' => function ($query) {
                $query->with(['user']);
            }
        ])->find($id);
        return new TaskResource($task);
    }

    public function progress_lists($id)
    {
        $task = Task::find($id);
        $progress_lists = $task->progress_lists->all();
        return Progress_listResource::collection($progress_lists);
    }
}
