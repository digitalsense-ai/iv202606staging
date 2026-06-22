<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ocr\ManualInputController;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:super-admin|team-user|company-admin',
])->group(function () {
    Route::get('analyzepdf/manual-input', [ManualInputController::class, 'index'])
        ->name('analyze.pdf.manual-input');
});
