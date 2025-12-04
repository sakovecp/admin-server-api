<?php

use App\Http\Controllers\Api\V1\Server\ServerController as ServerV1;
use App\Http\Controllers\Api\V1\Server\VhostController as VhostV1;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api'])->group(function () {
    Route::prefix('server')->group(function(){
        Route::post('start', [ServerV1::class, 'start']);
        Route::post('stop', [ServerV1::class, 'stop']);
        Route::post('restart', [ServerV1::class, 'restart']);
        Route::post('reload', [ServerV1::class, 'reload']);
    });

    Route::prefix('vhosts')->group(function () {
        Route::get('/', [VhostV1::class, 'all']);
        Route::post('/', [VhostV1::class, 'create']);
        Route::delete('/{domain}', [VhostV1::class, 'delete']);
    });
});
