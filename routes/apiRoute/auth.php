<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/authenthicate', [AuthController::class, 'authenthicate']);
Route::post('/register', [AuthController::class, 'register']);

Route::group([
  'middleware' => ['jwt.verify'],
  'prefix' => 'auth'
], function () {
  Route::delete('/logout', [AuthController::class, 'logout']);
  Route::post('/refresh', [AuthController::class, 'refresh']);
  Route::get('/user', [AuthController::class, 'user']);
});
