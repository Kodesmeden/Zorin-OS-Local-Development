<?php

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::controller( AppController::class )->group( function() {
//     Route::get( '/', 'index' )->name( 'dashboard' );
//     Route::get( '/phpinfo', 'phpinfo' )->name( 'phpinfo' );

//     if ( function_exists( 'xdebug_info' ) ) {
//         Route::get( '/xdebug', 'xdebug' )->name( 'xdebug' );
//     }

//     // Route::get( '/logs', 'logs' )->name( 'logs' );
//     // Route::post( '/logs', 'get_log' )->name( 'get-log' );
//     // Route::post( '/clear-log', 'clear_log' )->name( 'clear-log' );
// } );