<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['as' => 'api.'], function () {
    Route::post('login', [App\Http\Controllers\Api\AuthController::class, 'login'])->name('login');

    Route::group(['middleware' => ['throttle:3000,1', 'auth:sanctum']], function () {
        Route::apiResource('files', App\Http\Controllers\Api\FileController::class)->only([
            'index',
            'store',
            'show',
            'destroy',
        ]);
        Route::get('files/{file}/download', [App\Http\Controllers\Api\FileController::class, 'download'])->name('files.download');
    });
});
