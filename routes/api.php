<?php

use App\Http\Controllers\FlowController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/save-workflow', [FlowController::class, 'store']);
Route::get('/workflows', [FlowController::class, 'index']);