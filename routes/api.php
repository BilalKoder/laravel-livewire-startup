<?php

use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\Progress_listApiController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\AppointmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// https://documenter.gettaskman.com/view/8942795/TVmJhJv2

Route::post('registration', [UserApiController::class, 'store']);
Route::post('login', [UserApiController::class, 'login']);
Route::post('forgot-password', [UserApiController::class, 'forgetPassword']);
Route::post('verify-otp', [UserApiController::class, 'verifyOtp']);
Route::post('reset-password', [UserApiController::class, 'resetPassword']);
Route::get('show/{id}', [UserApiController::class, 'show']);


Route::get('user/{id}', [UserApiController::class, 'show']);
Route::get('user/{id}/update', [UserApiController::class, 'update']);
Route::get('user/{id}/tasks', [UserApiController::class, 'tasks']);
Route::get('user/{id}/progress_lists', [UserApiController::class, 'progress_lists']);

Route::get('categories', [CategoryApiController::class, 'index']);
Route::get('categories/{id}/tasks', [CategoryApiController::class, 'tasks']);

Route::get('tasks', [TaskApiController::class, 'index']);
Route::get('tasks/{id}', [TaskApiController::class, 'show']);
Route::get('tasks/{id}/progress_lists', [TaskApiController::class, 'progress_lists']);
Route::get('delete/tasks', [TaskController::class, 'deleteAllTask']);


Route::middleware('auth:sanctum')->group(function () {

        /** Task & Progress Routes Start*/

        //this route will GET all tasks or filtered with query param

        Route::get('all/tasks', [TaskController::class, 'index']);

        //this route will CREATE a new task

        Route::post('task', [TaskController::class, 'store']);

        //this route will GET a task by ID

        Route::get('task/{id}', [TaskController::class, 'show']);

        //this route will DELETE a task by ID

        Route::get('task/{id}/destory', [TaskController::class, 'delete']);

        //this route will UPDATE a task by ID

        Route::post('task/{id}/update', [TaskController::class, 'update']);

        //this route will CREATE a new task PROGRESS

        Route::post('task/{id}/progress', [TaskController::class, 'storeProgress']);


        //this route will GET all Appointments with filter

        Route::get('appointments', [AppointmentController::class, 'index']);

        //this route will send request to coach

        Route::post('appointment', [AppointmentController::class, 'store']);

        //this route will GET appointment by ID

        Route::get('appointment/{id}', [AppointmentController::class, 'show']);

        //this route will DELETE a appointment by ID

        Route::get('appointment/{id}/destory', [AppointmentController::class, 'delete']);

        //this route will UPDATE a appointment by ID

        Route::post('appointment/{id}/update', [AppointmentController::class, 'update']);

        //this route will get progress analytics

        Route::get('analytics', [TaskController::class, 'analytics']);

        /**Task & Progress Routes End */

          /**Categories Routes  */

        Route::get('all/categories', [CategoryApiController::class, 'getAllCategories']);

        Route::post('categories', [CategoryApiController::class, 'store']);

         /**Categories Routes  */

    Route::post('progress_lists/tasks', [Progress_listApiController::class, 'store']);

    /**Logout Route  */

    Route::post('logout', [UserApiController::class, 'logout']);
    
    Route::post('update-password',[UserApiController::class, 'updatePassword']);
});
