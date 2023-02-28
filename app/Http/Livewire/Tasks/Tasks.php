<?php
namespace App\Http\Livewire\Tasks;

use App\Models\Category;
use App\Models\Image;
use App\Models\Task;
use App\Models\UserTask;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;


class Tasks extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $title, $description, $category_id, $task_id , $image,$goal;
    public $photos = [];
    public $isOpen = 0;

    public function render()
    {
        return view('livewire.tasks.tasks', [
            'tasks' => UserTask::orderBy('id', 'desc')->paginate(),
            'categories' => Category::all(),
        ]);
    }

    public function store()
    {
        $this->validate([
            'title' => 'required',
            'description' => 'required',
            'category_id' => 'required',
            'image' => 'image|max:1024',
        ]);

        // if($this->image){
        //     $image = $this->image;
            //store Image to directory
            // $imgName = rand() . '_' . time() . '.' . $image->getClientOriginalExtension();
            // $destinationPath = public_path('task_icons');
            // $imagePath = $destinationPath . "/" . $imgName;
            // $image->move($destinationPath, $imgName);
            // $path = "task_icons" . "/" .basename($imagePath);
           
        // }
        $storedImage = $this->image->store('public/task_icons');

        // Update or Insert Task
        $task = UserTask::updateOrCreate(['id' => $this->task_id], [
            'title' => $this->title,
            'goal' => $this->goal,
            'description' => $this->description,
            'category_id' => intVal($this->category_id),
            'user_id' => Auth::user()->id,
            'image' => url('storage'. Str::substr($storedImage, 6)),
        ]);



        // Image upload and store name in db
        // if (count($this->photos) > 0) {
        //     Image::where('task_id', $task->id)->delete();
        //     $counter = 0;
            // foreach ($this->photos as $photo) {

            //     $storedImage = $photo->store('public/photos');

            //     $featured = false;
            //     if($counter == 0 ){
            //         $featured = true;
            //     }
                // Image::create([
                //     'url' => url('storage'. Str::substr($storedImage, 6)),
                //     'title' => '-',
                //     'task_id' => $task->id,
                //     'featured' => $featured
                // ]);
                // $counter++;
        //     }
        // }

        session()->flash(
            'message',
            $this->task_id ? 'Task Updated Successfully.' : 'Task Created Successfully.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        UserTask::find($id)->delete();

        session()->flash('message', 'Task Deleted Successfully.');
    }

    public function edit($id)
    {
        $task = UserTask::findOrFail($id);

        $this->task_id = $id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->category = $task->category_id;

        $this->openModal();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields()
    {
        $this->title = null;
        $this->description = null;
        $this->category = null;
        $this->photos = null;
        $this->task_id = null;
    }
}
