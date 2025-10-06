<?php

use Illuminate\Support\Facades\Route;
use ESolution\LaravelEmail\Http\Controllers\{TemplateController,BroadcastController,WebhookController,TrackingController,SuppressionController};

Route::prefix('laravel-email')->group(function(){
    // templates
    Route::post('/templates', [TemplateController::class, 'store']);
    Route::get('/templates', [TemplateController::class, 'index']);

    // broadcasts
    Route::post('/broadcasts', [BroadcastController::class, 'create']);
    Route::post('/broadcasts/{broadcast}/recipients', [BroadcastController::class, 'addRecipients'])->whereNumber('broadcast');
    Route::post('/broadcasts/{broadcast}/start', [BroadcastController::class, 'start'])->whereNumber('broadcast');

    // webhook
    Route::post('/webhook/sendgrid', [WebhookController::class, 'sendgrid'])->name('laravel_email.webhook.sendgrid');

    // tracking & unsubscribe
    Route::get('/t/{token}', [TrackingController::class, 'pixel'])->name('laravel_email.track');
    Route::get('/u/{token}', [TrackingController::class, 'unsubscribe'])->name('laravel_email.unsubscribe');

    // suppression list
    Route::get('/suppressions', [SuppressionController::class, 'index']);
    Route::post('/suppressions', [SuppressionController::class, 'store']);
    Route::delete('/suppressions/{suppression}', [SuppressionController::class, 'destroy'])->whereNumber('suppression');
});
