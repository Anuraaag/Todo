<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TodoController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/store', [TodoController::class, 'store']);
Route::get('/index', [TodoController::class, 'index']);
Route::delete('todo/{task}', [TodoController::class, 'destroy']);
Route::patch('todo/{task}', [TodoController::class, 'update']);

Route::get('/pending', [TodoController::class, 'listPendingTasks']);
Route::get('/pending_filtered', [TodoController::class, 'filterPendingTasks']);
Route::get('/search/{term}', [TodoController::class, 'searchTasks']);