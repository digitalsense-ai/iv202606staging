<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\ApiController;
use App\Http\Controllers\api\InvoiceController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/login', [ApiController::class, 'login']);

Route::middleware([
    'auth:sanctum'    
])->group(function () {
	Route::get('/companies', [InvoiceController::class, 'companies']);
	Route::get('/invoices/{vat_reg_id}', [InvoiceController::class, 'index']);
});