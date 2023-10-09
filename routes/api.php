<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminArticleController;
use App\Http\Controllers\AdminWordController;
use App\Http\Controllers\AdminTechnologyController;


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
Route::get('/article/generate',[AdminArticleController::class,'generate']);
Route::get('/article/list',[AdminArticleController::class,'list']);
Route::post('/article/save',[AdminArticleController::class,'save']);
Route::get('/article/count',[AdminArticleController::class,'count']);


//admin word
Route::get('/word/generate',[AdminWordController::class,'generate']);
Route::get('/word/list',[AdminWordController::class,'list']);
Route::post('/word/save',[AdminWordController::class,'save']);
Route::get('/word/count',[AdminWordController::class,'count']);


//admin technology
Route::get('/technology',[AdminTechnologyController::class,'index']);
Route::post('/technology/save',[AdminTechnologyController::class,'save']);