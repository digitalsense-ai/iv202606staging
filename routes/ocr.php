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

    Route::get('analyzepdf/manual-input/queue', [ManualInputController::class, 'queue'])
        ->name('analyze.pdf.manual-input.queue');

    Route::get('analyzepdf/manual-input/client-lookup', [ManualInputController::class, 'clientLookup'])
        ->name('analyze.pdf.manual-input.client-lookup');

    Route::get('analyzepdf/manual-input/{id}', [ManualInputController::class, 'show'])
        ->whereNumber('id')
        ->name('analyze.pdf.manual-input.show');

    Route::post('analyzepdf/manual-input/{id}/save', [ManualInputController::class, 'save'])
        ->whereNumber('id')
        ->name('analyze.pdf.manual-input.save');

    Route::post('analyzepdf/manual-input/{id}/force-submit', [ManualInputController::class, 'forceSubmit'])
        ->whereNumber('id')
        ->name('analyze.pdf.manual-input.force-submit');

    Route::delete('analyzepdf/manual-input/{id}', [ManualInputController::class, 'destroy'])
        ->whereNumber('id')
        ->name('analyze.pdf.manual-input.delete');
});
