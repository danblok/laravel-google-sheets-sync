<?php

use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::post('/items/generate', [ItemController::class, 'generate'])->name('items.generate');
Route::delete('/items/clear', [ItemController::class, 'clear'])->name('items.clear');
Route::resource('items', ItemController::class);

Route::post('/google-sheet-url', [ItemController::class, 'updateGoogleSheetUrl'])->name('google.sheet.url');
Route::post('/test-google-connection', [ItemController::class, 'testGoogleConnection'])->name('test.google.connection');

Route::get('/fetch/{count?}', function ($count = null) {
    $command = 'sheets:fetch';
    $parameters = [];

    if ($count && is_numeric($count)) {
        $parameters['--count'] = $count;
    }

    try {
        Artisan::call($command, $parameters);
        $output = Artisan::output();

        return response('<pre>'.htmlspecialchars($output).'</pre>')
            ->header('Content-Type', 'text/html; charset=UTF-8');
    } catch (\Exception $e) {
        return response('<pre>Error: '.htmlspecialchars($e->getMessage()).'</pre>')
            ->header('Content-Type', 'text/html; charset=UTF-8');
    }
})->where('count', '[0-9]+');
