<?php

namespace App\Http\Controllers;

use App\Http\Base\Controller;
use App\Logic\AppLogic;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function index() {
        $phpVersions = array_map('basename', glob('/etc/php/*'));
        return view( 'pages.dashboard', ['phpVersions' => $phpVersions] );
    }

    public function store(Request $request) {
        $appLogic = new AppLogic();

        $siteCreated = $appLogic->createApp($request->input('name'), $request->input('type'), $request->input('php_version'), $request->input('repo'));

        
        dd($siteCreated);
        dd($request->input());
    }

    public function update(Request $request){

    }

    public function phpinfo() {
        ob_start();

        phpinfo();

        return ob_get_clean();
    }

    public function xdebug() {
        if ( ! function_exists( 'xdebug_info' ) ) {
            return;
        }

        ob_start();

        xdebug_info();

        return ob_get_clean();
    }

    // public function logs() {
    //     $data = [];
    //     $logs_path = dirname( base_path() ) . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR;

    //     $i = 1;
    //     foreach ( glob( $logs_path . '*.log' ) as $log_file ) {
    //         $data['logs'][ basename( $log_file ) ] = $log_file;

    //         if ( $i === 1 ) {
    //             $data['default_log_content'] = @file_get_contents( $log_file );
    //         }

    //         ++$i;
    //     }

    //     return view( 'logs.index', compact( 'data' ) );
    // }

    // public function get_log() {
    //     $log_path = request()->input( 'path' );

    //     return @file_get_contents( $log_path );
    // }

    // public function clear_log() {
    //     $log_path = request()->input( 'path' );

    //     try {
    //         return (bool) file_put_contents( $log_path, '--- File created by Wampserver installation ---' );
    //     } catch( \Exception $e ) {
    //         return false;
    //     }
    // }
}
