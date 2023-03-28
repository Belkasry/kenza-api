<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/csv-to-json', [\App\Http\Controllers\ApiController::class, 'csvToJson']);
Route::post('/csv', [\App\Http\Controllers\ApiController::class, 'csvupload']);
Route::get('/template', [\App\Http\Controllers\ApiController::class, 'getTemplateKeys']);
Route::post('/template', [\App\Http\Controllers\ApiController::class, 'updateTemplateKeys']);
Route::post('/config', [\App\Http\Controllers\ApiController::class, 'saveConfig']);
Route::get('/mappings', [\App\Http\Controllers\ApiController::class, 'getMappings']);
Route::get('/template_prestataire', [\App\Http\Controllers\ApiController::class, 'loadTemplate']);

Route::post('/export', function (Request $request) {
    $data = $request->input('data');
    $mapping = $request->input('mapping');
    $template = $request->input('template');

    return (new \App\Http\Controllers\ExcelExportController($data, $mapping, $template))->export('export.xlsx');
});
