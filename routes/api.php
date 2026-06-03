<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\ApiController;
use App\Http\Controllers\api\InvoiceController;

use App\Http\Controllers\api\OcrGptController;

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

// Route::get('/token/ocr', function() {dd("6666666");
//     $user = auth()->user();
//     $token = $user->tokens()->where('name', 'OCR API Token')->first();dd($token);
//     return response()->json([
//         'token' => $token ? $token->plainTextToken : null
//     ]);
// })->middleware(['auth:web']);

Route::middleware([
    'auth:sanctum'    
])->group(function () {
	Route::get('/companies', [InvoiceController::class, 'companies']);
	Route::get('/invoices/{vat_reg_id}', [InvoiceController::class, 'index']);

	Route::get('/testorgno/{org_no}', [OcrGptController::class, 'index']);

	Route::get('/ocrinvoices/{org_no}', [OcrGptController::class, 'showForClient']);
});