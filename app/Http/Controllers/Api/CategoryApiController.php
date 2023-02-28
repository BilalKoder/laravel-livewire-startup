<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\TaskResource;
use App\Models\Category;
use DB;
use App\Models\Task;
use Illuminate\Http\Request;

class CategoryApiController extends BaseController
{
    public function index()
    {
        //See if user has certain abilities
        // if (!auth()->user()->tokenCan('categories-list')) {
        //     abort(403, 'Unauthorized');
        // }

        $categories = Category::all();
        // if ($categories == null) {
        //     return response()->json(['message' => 'Not Found!'], 404);
        // }
        return CategoryResource::collection($categories);
    }

    public function tasks($id)
    {
        $tasks = Task::where('category_id', $id)->orderBy('id', 'desc')->paginate();
        return TaskResource::collection($tasks);
    }

    public function store(Request $request)
    {
        # code...
        try{

        DB::beginTransaction();
              
            $categories = new Category;
            $categories->title = $request->title? $request->title:'';
            $categories->color = $request->color? $request->color : '#fff';
            // $categories->meta_data = $request->meta_data ? $request->meta_data:'';
            $categories->save();

            DB::commit();

            return $this->sendResponse($categories,"Categories Created Successfully!");

        } catch (\Throwable $th) {
            
            DB::rollBack();
            return $this->sendError('Something went wrong', $th->getMessage());     
        }
    }

    public function getAllCategories()
    {
        # code...
        $categories = Category::all();
        return $this->sendResponse($categories,"All Categories");

    }
}
