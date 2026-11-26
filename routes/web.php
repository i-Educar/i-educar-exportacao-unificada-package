<?php

use iEducar\Packages\BuritiExportacaoUnificada\Http\Controllers\ExportacaoController;
use Illuminate\Support\Facades\Route;

Route::prefix('exportacao')->group(function (): void {
    Route::get('/', [ExportacaoController::class, 'index']);
    Route::post('/config', [ExportacaoController::class, 'salvarConfig']);
    Route::get('/demo', [ExportacaoController::class, 'demo']);
    Route::post('/demo/emitir', [ExportacaoController::class, 'emitirDemo']);
});
