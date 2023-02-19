<?php

use App\Http\Livewire\Categories\Categories;
use App\Http\Livewire\Categories\Categorytasks;
use App\Http\Livewire\Tasks\Tasks;
use App\Http\Livewire\Users\Users;
use App\Http\Livewire\Tasks\Task as t;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('dashboard/users', Users::class)->name('users');
Route::get('dashboard/users/{id}', Users::class);

Route::get('dashboard/categories', Categories::class)->name('categories');
Route::get('dashboard/categories/{id}/tasks', Categorytasks::class);

Route::get('dashboard/tasks', Tasks::class)->name('tasks');
Route::get('dashboard/tasks/{id}', t::class);
