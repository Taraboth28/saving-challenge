<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Saving Challenge
|--------------------------------------------------------------------------
*/

// Dashboard stats
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

// Goals (CRUD)
Route::apiResource('goals', GoalController::class);

// History entries per goal
Route::get('/transactions', [HistoryController::class, 'report']);
Route::get('/goals/{goalId}/history', [HistoryController::class, 'index']);
Route::post('/goals/{goalId}/history', [HistoryController::class, 'store']);
Route::delete('/history/{id}', [HistoryController::class, 'destroy']);
