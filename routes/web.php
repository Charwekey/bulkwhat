<?php

use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentCategoryController;
use App\Http\Controllers\TemplateCategoryController;
use App\Http\Controllers\TemplateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Student Contact Categories
    Route::resource('categories', StudentCategoryController::class);
    Route::get('categories/{category}/upload', [StudentCategoryController::class, 'uploadForm'])->name('categories.upload');
    Route::post('categories/{category}/upload', [StudentCategoryController::class, 'processUpload'])->name('categories.process-upload');

    // Message Templates & Categories
    Route::resource('templates', TemplateController::class);
    Route::get('api/templates-json', [TemplateController::class, 'getTemplatesJson'])->name('templates.json');
    Route::resource('template-categories', TemplateCategoryController::class)->only(['store', 'update', 'destroy']);

    // Imports
    Route::resource('imports', ImportController::class)->except(['edit', 'update']);
    Route::get('imports/{import}/preview', [ImportController::class, 'preview'])->name('imports.preview');
    Route::post('imports/{import}/process', [ImportController::class, 'process'])->name('imports.process');

    // Campaigns
    Route::resource('campaigns', CampaignController::class);
    Route::get('campaigns/{campaign}/preview', [CampaignController::class, 'preview'])->name('campaigns.preview');
    Route::post('campaigns/{campaign}/test', [CampaignController::class, 'testSend'])->name('campaigns.test');
    Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');

    // Messages
    Route::get('campaigns/{campaign}/messages', [MessageController::class, 'index'])->name('messages.index');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/test-connection', [SettingsController::class, 'testConnection'])->name('settings.test');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
