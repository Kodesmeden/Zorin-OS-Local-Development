<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\DatabaseController;
use Illuminate\Support\Facades\Route;

Route::controller( AppController::class )->group( function() {
    Route::get( '/', 'index' )->name( 'dashboard' );
    Route::post( '/', 'store' )->name( 'create-website' );

    Route::get( '/phpinfo', 'phpinfo' )->name( 'phpinfo' );

    if ( function_exists( 'xdebug_info' ) ) {
        Route::get( '/xdebug', 'xdebug' )->name( 'xdebug' );
    }

    // Route::get( '/logs', 'logs' )->name( 'logs' );
    // Route::post( '/logs', 'get_log' )->name( 'get-log' );
    // Route::post( '/clear-log', 'clear_log' )->name( 'clear-log' );
} );

Route::controller( DatabaseController::class )->prefix('/databases')->group( function() {
    Route::get( '/', 'index' )->name( 'databases' );
} );