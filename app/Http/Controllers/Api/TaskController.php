<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\UserTask;
use App\Models\Progress;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAssignedTask;
use Validator;
use DB;
use Carbon\Carbon;

class TaskController extends BaseController
{

//     const WEEKLY = 10;
// const MONTHLY = 20;
// const YEARLY   = 30;

// 1 = professional
// 2= personal
    
    public function index(Request $request)
    {

        // $tasks = UserTask::query();
        $tasks = UserAssignedTask::query();

        if($request->category_id){
            // $tasks->where('category_id',$request->category_id);

            $tasks->whereHas(['task' => function($query) use ($request->category_id)  {
                $query->where('category_id', $request->category_id);
            }]);
       }
    //     if($request->id){
    //         $tasks->where('id',$request->id);
    //    }
        if($request->user_id){
            // $tasks->where('user_id',$request->user_id);
            $tasks->whereHas(['task' => function($query) use ($request->user_id)  {
                $query->where('user_id', $request->user_id);
            }]);
       }
        if($request->created_at){
            // $tasks->where('created_at',Carbon::parse($request->created_at)->format('Y M d'));
            $tasks->where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d'))"),Carbon::parse($request->created_at)->format('Y-m-d'));
            // $tasks->whereHas(['task' => function($query) use ($request->user_id)  {
            //     $query->where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d'))"),Carbon::parse($request->created_at)->format('Y-m-d'));
            // }]);
       }

        if($request->type && $request->type == "10"){
            $tasks->whereBetween('created_at', 
                [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
            );
       }

       if($request->type && $request->type == "20"){
            $tasks->whereBetween('created_at', 
                [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]
            );
        }

        if($request->type && $request->type == "30"){
            $tasks->whereBetween('created_at', 
                [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]
            );
        }

        $result = $tasks->paginate();

        if($result){

            for ($i=0; $i < count($result); $i++) { 
                # code...

                  $prevProgressCount = DB::table('progress')
                    ->where('progress.task_id', '=', $result[$i]['id'])
                    ->sum('progress.progress_value');
                    $result[$i]['totalProgress'] = $prevProgressCount;
                    $result[$i]['totalPercent']= round(($prevProgressCount/ $result[$i]['goal'])*100); 

            }
        }

        return $this->sendResponse($result, 'All Tasks Listing');
    }


    public function store(Request $request)
    {
      
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'goal' => 'required'
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        try {

            if($request->file('image')){
                $image = $request->file('image');
                //store Image to directory
                $imgName = rand() . '_' . time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('task_icons');
                $imagePath = $destinationPath . "/" . $imgName;
                $image->move($destinationPath, $imgName);
                $path = "task_icons" . "/" .basename($imagePath);
            }
            
            DB::beginTransaction();
              
            $task = new UserTask;
            $task->title = $request->title;
            $task->description = $request->description;
            $task->goal = $request->goal;
            $task->type = $request->type ? $request->type : "WEEKLY";
            $task->user_id = auth()->user()->id;
            $task->image = $request->file('image') ? $path : '';
            $task->category_id = $request->category_id ? $request->category_id : '2';
            $task->save();

            $assignedTask = new UserAssignedTask;
            $assignedTask->task_id = $task->id;
            $assignedTask->user_id = auth()->user()->id;
        
            $assignedTask->save();

            DB::commit();

            return $this->sendResponse($task,"Task Created Successfully!");

        } catch (\Throwable $th) {
            
            DB::rollBack();
            return $this->sendError('Something went wrong', $th->getMessage());     
        }
      
    }

    public function show($id){

      
        // $task = UserTask::with('progress')->find($id);
        $task = UserAssignedTask::with('task','task.progress')->find($id);

        if(!$task){
            return $this->sendError('Task By this ID doest not exist', null);       
        }

        $preRecord = [];

        $record = [];

        $prevProgressCount = DB::table('progress')
        ->where('progress.task_id', '=', $task->id)
        ->sum('progress.progress_value');
      
        $task->totalProgress = $prevProgressCount;
        $task->totalPercent = round(($prevProgressCount/ $task->task()->goal)*100); 

        $allProgress = Progress::where('task_id',$task->id)->get();

        if($allProgress){
            foreach($allProgress as $key => $value){
                $record['date'] = Carbon::parse($value['created_at'])->format("m-d-Y");
                $record['value'] = $value['progress_value'];
                array_push($preRecord,(object)$record);
            }
        }

        $task->allProgress = $preRecord;

        return $this->sendResponse($task, 'Task Listing');
    }

    public function delete($id){
      
        $task = UserAssignedTask::where('task_id',$id)->find();

        if(!$task){
            return $this->sendError('Task By this ID doest not exist', null);       
        }

        $task->delete();

        return $this->sendResponse(null, 'Task Deleted Successfully!');
    }

    public function update(Request $request,$id){
      
        $task = UserTask::find($id);

        if(!$task){
            return $this->sendError('Task By this ID doest not exist', null);       
        }

        $task->title = $request->title ? $request->title : $task->title;
        $task->description = $request->description ? $request->description : $task->description;
        $task->goal = $request->goal ? $request->goal : $task->goal;
        $task->type = $request->type ? $request->type : $task->type;
        $task->category_id = $request->category_id ? $request->category_id : $task->category_id;

        $task->save();

        return $this->sendResponse($task, 'Task Updated Successfully!');
    }

    public function storeProgress(Request $request,$id)
    {

        // $task = UserTask::find($id);
       $task = UserAssignedTask::find($id);

        if(!$task){
            return $this->sendError('Task By this ID doest not exist', null);       
        }

        $validator = Validator::make($request->all(), [
            'progress_value' => 'required',
            'progress_date' => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        if($request->progress_value > $task->task->goal){
            return $this->sendError('Progress Value can not be greater than Total Goal', null);       
        }

        // $prevProgressCount = DB::table('progress')
        //     ->where('progress.task_id', '=', $id)
        //     ->sum('progress.progress_value');

        // if($prevProgressCount == $task->goal){
        //     return $this->sendError('You have already completed Task', null);       
        // }

    //    $assignedTask = UserAssignedTask::where('task_id',$id)->where('user_id',auth()->user()->id)->find();

        try {
            
            DB::beginTransaction();
              
            $progress = new Progress;
            $progress->progress_value = $request->progress_value;
            $progress->progress_date = $request->progress_date;
            $progress->task_id = $id;
            $progress->user_id = auth()->user()->id;
            $progress->save();

            DB::commit();

            return $this->sendResponse($progress,"Progress Created Successfully!");

        } catch (\Throwable $th) {
            
            DB::rollBack();
            return $this->sendError('Something went wrong', $th->getMessage());     
        }
      
    }

    public function analytics(Request $request){
       
        $data = 0;

        $totalProgressCount= 0;

        if(!$request->user_id){
            $this->sendError('User ID is required, Please send user_id in query param.', null);
        }

        // $tasks  = UserTask::latest();
        $tasks  = UserAssignedTask::latest();
      
       if($request->type && $request->type == "10"){
            $tasks->whereBetween('created_at', 
                [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
            );

            $totalProgressCount = Progress::where('user_id',$request->user_id)
            ->whereBetween('created_at', 
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                )->sum('progress_value');
       }

       if($request->type && $request->type == "20"){
            $tasks->whereBetween('created_at', 
                [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]
            );

            $totalProgressCount = Progress::where('user_id',$request->user_id)
            ->whereBetween('created_at', 
                    [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]
                )->sum('progress_value');
        }

        if($request->type && $request->type == "30"){
            $tasks->whereBetween('created_at', 
                [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]
            );
            $totalProgressCount = Progress::where('user_id',$request->user_id)
            ->whereBetween('created_at', 
                    [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()]
                )->sum('progress_value');
       }

       
       if($request->user_id){
           $tasks->where('user_id',$request->user_id);
        // $tasks->with(['task' => function ($query) {
        //     $query->where('trashed', '<>', 1);
        // }])->get();
        }
        // $totalSumOfGoals = $tasks->sum('goal');

        $totalSumOfGoals = 0;

        $allTask = $tasks->get();

        if($allTask){
            foreach ($allTask as $key => $value) {
                $totalSumOfGoals += $value['goal'];
            }
        }

        // $totalSumOfGoals = $tasks
        // ->with(['task' => function ($query) {
        //         $query->where('trashed', '<>', 1);
        //     }])->sum('goal');
     
        
        if($totalSumOfGoals == 0){
            return $this->sendResponse($data,"User Analytics",);
        }


       $data = round(($totalProgressCount/ $totalSumOfGoals)*100); 

     
        return $this->sendResponse($data,"User Analytics",);
    }

    public function deleteAllTask()
    {
        # code...

        UserTask::truncate();
        Progress::truncate();

        return response()->json("Deleted Successfully!");
    }

}
