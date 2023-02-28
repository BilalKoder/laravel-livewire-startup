<?php

namespace App\Http\Livewire\Tasks;

use App\Models\Task as TaskModel;
use App\Models\UserTask;
use Livewire\Component;

class Task extends Component
{

    public $task;

    public function mount($id)
    {
        $this->task = UserTask::with(['user', 'progress', 'category'])->find($id);
    }

    public function render()
    {
        return view('livewire.tasks.task');
    }
}
