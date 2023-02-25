<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\UserTask;
use App\Models\Progress;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\Appointments;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;

class AppointmentController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $appointments = Appointments::query();

        foreach ($request->query() as $key => $value)
        {
            $appointments->where($key,$value);
        }

        $result = $appointments->paginate();

        return $this->sendResponse($result, 'All Appointment Listing');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            
            'topic' => 'required',
            'preferred_date' => 'required',
            'preferred_time' => 'required',
            'message' => 'required',
      
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        try {
            
            DB::beginTransaction();
              
            $appointment = new Appointments;
            $appointment->topic = $request->topic;
            $appointment->preferred_date = $request->preferred_date;
            $appointment->preferred_time = $request->preferred_time;
            $appointment->message = $request->message;
            $appointment->user_id = auth()->user()->id;
            $appointment->save();

            DB::commit();

            return $this->sendResponse($appointment,"Appointment Created Successfully!");

        } catch (\Throwable $th) {
            
            DB::rollBack();
            return $this->sendError('Something went wrong', $th->getMessage());     
        }
    }

    public function show($id)
    {
        $appointment = Appointments::find($id);

        if(!$appointment){
            return $this->sendError('Appointment By this ID doest not exist', null);       
        }

        return $this->sendResponse($appointment, 'Appointment Listing');
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            
            'topic' => 'required',
            'preferred_date' => 'required',
            'preferred_time' => 'required',
            'status' => 'required|in:PENDING,APPROVED,COMPLETED',
            'message' => 'required',
      
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $appointment = Appointments::find($id);

        if(!$appointment){
            return $this->sendError('appointment By this ID doest not exist', null);       
        }

        $appointment->topic = $request->topic ? $request->topic : $appointment->topic;
        $appointment->preferred_date = $request->preferred_date ? $request->preferred_date : $appointment->preferred_date;
        $appointment->preferred_time = $request->preferred_time ? $request->preferred_time : $appointment->preferred_time;
        $appointment->status = $request->status ? $request->status : $appointment->status;
        $appointment->message = $request->message ? $request->message : $appointment->message;

        $appointment->save();

        return $this->sendResponse($appointment, 'Appointment Updated Successfully!');    }

    public function destroy($id)
    {
        $appointment = Appointments::find($id);

        if(!$appointment){
            return $this->sendError('appointment By this ID doest not exist', null);       
        }

        $appointment->delete();

        return $this->sendResponse(null, 'appointment Delete Successfully!');
    }
}
