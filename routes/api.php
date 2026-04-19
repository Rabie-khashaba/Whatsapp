<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ClientTokenController;
use App\Http\Controllers\Api\InstanceApiController;
use App\Http\Controllers\Api\WhatsappController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->post('/generate-token', [ClientTokenController::class, 'generate']);

// Route::middleware('api.auth')->post(
//     '/send-message',
//     [WhatsappController::class, 'sendMessage']
// );


Route::post(
    '/send-message',
    [WhatsappController::class, 'sendMessage']
);


Route::post('/webhook/baileys', [WebhookController::class, 'handle']);

Route::prefix('v2')->group(function () {
    Route::get('/instances', [InstanceApiController::class, 'index']);
    Route::get('/instances/{instance_id}', [InstanceApiController::class, 'show']);
});

// Paymob redirect/callback (some dashboards require /api/* URLs)
Route::match(['GET', 'POST'], '/payment/callback', [PaymentController::class, 'paymobCallback']);
Route::match(['GET', 'POST'], '/payment/fawaterk/callback', [PaymentController::class, 'fawaterkCallback']);
