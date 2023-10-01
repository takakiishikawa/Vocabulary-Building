<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminArticleController;
use App\Http\Controllers\AdminWordController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//admin article
Route::get('/article',[AdminArticleController::class,'index']);
Route::post('/article/save',[AdminArticleController::class,'save']);

//admin word
Route::get('/word',[AdminWordController::class,'index']);
Route::post('/word/save',[AdminWordController::class,'save']);
