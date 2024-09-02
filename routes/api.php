<?php

use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\PortalController;
use App\Http\Controllers\Portal\PortalController as PortalPortalController;
use App\Http\Controllers\Subscriber\SubscriberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Test\TestController;
use App\Http\Controllers\Api\PosCartExtensionController;
use App\Http\Controllers\Api\CustomerController;
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

Route::get('/test', [TestController::class, 'test']);
Route::group(['middleware' => ['cors']], function () {

    Route::group(['namespace' => 'Api'], function () {
        // 	Route::post('subscriber', 'PortalController@index');
        // Route::post('liquidPortalsubscriber', 'PortalController@liquidIndex')->name('liquidPortalsubscriber');
        Route::post('liquidPortalsubscriber', [PortalController::class, 'liquidIndex'])->name('liquidPortalsubscriber');
        Route::post('subscriber/store', [PortalController::class, 'store'])->name('portalstore');
        Route::get('/chageCurrency', [TestController::class, 'chageCurrency']);
        Route::get('/shippingCostRemove', [TestController::class, 'shippingCostRemove']);
        Route::get('/ordertest/{id}', [TestController::class, 'ordertest']);
        Route::post('/callFlowTrigger', [SubscriberController::class, 'callFlowTrigger']);

        //
        //     // product subscription extension routes
        //     Route::group(['middleware' => ['extension.auth']], function () {
        //    		Route::get('plan-groups', 'ExtensionController@planGroups');
        //    	    Route::post('get-plan', 'ExtensionController@getPlan');
        //         Route::post('create', 'ExtensionController@createPlan');
        //         Route::post('remove', 'ExtensionController@removeProduct');
        //         Route::post('add', 'ExtensionController@add');
        //    });
        // });
        Route::group(['namespace' => 'Subscriber'], function () {
            Route::get('country/{country?}', [SubscriberController::class, 'getCountry'])->name('countryapi');
        });
    });
});

Route::group(['prefix' => 'v1'], function () {
    // get active customer contracts
    Route::get('/validateCustomer', [CustomerController::class ,'validateCustomer'])->name('v1-validate-customer');
    //POS cart extension api routes
    Route::group(['middleware' => ['poscart.auth']], function () {
        Route::post('/promotions', [PosCartExtensionController::class, 'promotions'])->name('v1-pos-promotions');
        Route::post('/perform_action', [PosCartExtensionController::class, 'performAction'])->name('v1-perform-action');
        Route::post('/revert_action', [PosCartExtensionController::class, 'revertAction'])->name('v1-revert-action');
    });
});
// webhook call from aws
//   Route::post('/webhooks', '\Api\WebhookController@index')->name('aws-webhooks');
Route::post('/webhooks', [WebhookController::class, 'index'])->name('aws-webhooks');
