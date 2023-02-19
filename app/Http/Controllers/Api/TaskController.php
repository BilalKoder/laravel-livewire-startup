<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\UserTask;
use App\Models\Progress;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use Carbon\Carbon;

class TaskController extends BaseController
{
    
    public function index(Request $request)
    {

        $tasks = UserTask::query();

        foreach ($request->query() as $key => $value)
        {
            $tasks->where($key,$value);
        }

        $tasks->whereBetween('created_at', 
            [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
        );

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
            'goal' => 'required',
            'type' => 'required|in:WEEKLY,MONTHLY,YEARLY', 
            'user_id' => 'required',
            'category_id' => 'required',
      
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        try {
            
            DB::beginTransaction();
              
            $task = new UserTask;
            $task->title = $request->title;
            $task->description = $request->description;
            $task->goal = $request->goal;
            $task->type = $request->type;
            $task->user_id = $request->user_id;
            $task->category_id = $request->category_id;
            $task->save();

            DB::commit();

            return $this->sendResponse($task,"Task Created Successfully!");

        } catch (\Throwable $th) {
            
            DB::rollBack();
            return $this->sendError('Something went wrong', $th->getMessage());     
        }
      
    }

    public function show($id){
      
        $task = UserTask::with('progress')->find($id);
        $prevProgressCount = DB::table('progress')
        ->where('progress.task_id', '=', $id)
        ->sum('progress.progress_value');
        $task->totalProgress = $prevProgressCount;
        $task->totalPercent = round(($prevProgressCount/ $task->goal)*100); 

        if(!$task){
            return $this->sendError('Task By this ID doest not exist', null);       
        }

        return $this->sendResponse($task, 'Task Listing');
    }

    public function delete($id){
      
        $task = UserTask::find($id);

        if(!$task){
            return $this->sendError('Task By this ID doest not exist', null);       
        }

        $task->delete();

        return $this->sendResponse(null, 'Task Delete Successfully!');
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
        $task->user_id = $request->user_id ? $request->user_id : $task->user_id;
        $task->category_id = $request->category_id ? $request->category_id : $task->category_id;

        $task->save();

        return $this->sendResponse($task, 'Task Updated Successfully!');
    }

    public function storeProgress(Request $request,$id)
    {

        $task = UserTask::find($id);

        if(!$task){
            return $this->sendError('Task By this ID doest not exist', null);       
        }

        $validator = Validator::make($request->all(), [
            'progress_value' => 'required',
            'progress_date' => 'required',
            'user_id' => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        if($request->progress_value > $task->goal){
            return $this->sendError('Progress Value can not be greater than Total Goal', null);       
        }

        $prevProgressCount = DB::table('progress')
            ->where('progress.task_id', '=', $id)
            ->sum('progress.progress_value');

        if($prevProgressCount == $task->goal){
            return $this->sendError('You have already completed Task', null);       
        }

        try {
            
            DB::beginTransaction();
              
            $progress = new Progress;
            $progress->progress_value = $request->progress_value;
            $progress->progress_date = $request->progress_date;
            $progress->task_id = $id;
            $progress->user_id = $request->user_id;
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

        $tasks  = UserTask::latest();
      
       if($request->type == "WEEKLY"){
            $tasks->whereBetween('created_at', 
                [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
            );

            $totalProgressCount = Progress::where('user_id',$request->user_id)
            ->whereBetween('created_at', 
                    [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]
                )->sum('progress_value');
       }

       if($request->type == "MONTHLY"){
            $tasks->whereBetween('created_at', 
                [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]
            );

            $totalProgressCount = Progress::where('user_id',$request->user_id)
            ->whereBetween('created_at', 
                    [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]
                )->sum('progress_value');
        }

        if($request->type == "YEARLY"){
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
        }
        
        $totalSumOfGoals = $tasks->sum('goal');
        
        if($totalSumOfGoals == 0){
            return $this->sendResponse($data,"User Analytics",);
        }


       $data = round(($totalProgressCount/ $totalSumOfGoals)*100); 

        return $this->sendResponse($data,"User Analytics",);
    }

}
