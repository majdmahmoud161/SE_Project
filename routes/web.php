<?php

use App\Http\Controllers\FlowController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/save-workflow', [FlowController::class, 'store']);
Route::get('/workflows', [FlowController::class, 'index']);
Route::post('/chat/send', [ChatController::class, 'handleMessage']);
Route::get('/chat', function () {
    return view('chat');
});


