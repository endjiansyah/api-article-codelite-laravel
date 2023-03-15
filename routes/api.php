<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// ---------{category}-------
Route::get("/category", [CategoryController::class, "index"]);
Route::get("/category/{id}", [CategoryController::class, "show"]);
Route::post("/category", [CategoryController::class, "store"]);
Route::post("/category/{id}/edit", [CategoryController::class, "update"]);
Route::post("/category/{id}/delete", [CategoryController::class, "destroy"]);

// ---------{article}-------
Route::get("/article", [ArticleController::class, "index"]);
Route::get("/article/{id}", [ArticleController::class, "show"]);
Route::post("/article", [ArticleController::class, "store"]);
Route::post("/article/{id}/edit", [ArticleController::class, "update"]);
Route::post("/article/{id}/delete", [ArticleController::class, "destroy"]);
Route::post("/article/{id}/media", [ArticleController::class, "ArticleMediaCreate"]);
Route::get("/media/{idmedia}", [ArticleController::class, "showmedia"]);
Route::post("/media/{idmedia}/update", [ArticleController::class, "MediaUpdate"]);
Route::post("/media/{idmedia}/delete", [ArticleController::class, "destroymedia"]);