<?php

namespace App\Http\Livewire\Tasks;

use App\Models\Task as TaskModel;
use Livewire\Component;

class Task extends Component
{

    public $task;

    public function mount($id)
    {
        $this->task = TaskModel::with(['user', 'progress_lists', 'category', 'images', 'videos'])->find($id);
    }

    public function render()
    {
        return view('livewire.tasks.task');
    }
}
