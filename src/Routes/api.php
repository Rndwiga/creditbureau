<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Musoni\Metropol\Infrastructure\Services\ServicesTests;

Route::prefix('api/v1')->group(function () {

    Route::prefix('hooks')->group(function () {

        Route::prefix('mifos')->group(function (){
            Route::post('created/clients/','MetropolApiIntegration@handleMifosClientCreationNotification');
        });

        Route::prefix('payments')->group(function (){
            Route::post('stk','MetropolApiIntegration@handleStkPaymentNotifications');
        });

        Route::prefix('credit')->group(function (){
            Route::post('report','MetropolApiIntegration@getCreditReport');

            Route::post('simulate_response',function (){
                return (new ServicesTests())->testRequestData();
            });
        });

    });

    Route::prefix('credit/metropol')->group(function () {
        Route::post('/score', 'MetropolApiIntegration@exposedGetMetropolReportScore');
    });

    Route::prefix('creditbureau/metropol')->group(function () {
        Route::get('score/{nationalId}', 'MetropolApiIntegration@exposedGetFullMetropolReport');
    });
});

Route::fallback(function(){
    return response()->json([
        'message' => 'Ooopss.. Sorry, the route you are trying to access does not exist. Hmmm :-('], 404);
});
