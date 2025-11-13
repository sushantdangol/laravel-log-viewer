<?php

use App\Http\Controllers\LogViewerController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;

Route::get('/', function () {
    return redirect()->route('log.viewer');
});

Route::get('/log-viewer', [LogViewerController::class, 'show'])->name('log.viewer');
Route::post('/log-upload', [LogViewerController::class, 'upload'])->name('log.upload');
Route::get('/log-fetch/{projectId}', [LogViewerController::class, 'fetchRemote'])->name('log.fetch');

Route::post('/clear-session', function () {
    session()->forget(['remote_log_url', 'remote_log_temp_path']);
    return response()->json(['success' => true]);
})->name('session.clear');

Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
