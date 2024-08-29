<?php

namespace App\Http\Controllers;

use App\Http\Base\Controller;
use App\Logic\AppLogic;
use App\Logic\DatabaseLogic;
use App\Logic\InstallerLogic;
use App\Models\Website;
use Illuminate\Http\Request;

class AppController extends Controller
{
    protected $appLogic;

    public function __construct()
    {
        $this->appLogic = new AppLogic(new DatabaseLogic, new InstallerLogic);
    }

    public function index() {
        $phpVersions = array_map('basename', glob('/etc/php/*'));
        $applications = Website::all();
        return view( 'pages.dashboard', ['phpVersions' => $phpVersions, 'applications' => $applications] );
    }

    public function store(Request $request) {
        $siteCreated = $this->appLogic->createApp($request->input('name'), $request->input('type'), $request->input('php_version'), $request->input('repo'));

        if ($siteCreated) {
            return redirect()->route('dashboard')->with('success', 'Site created successfully');
        } else {
            return redirect()->route('dashboard')->with('error', 'Site creation failed');
        }
        
        // dd($siteCreated);
        // dd($request->input());
    }

    public function changePhpVersion(Request $request){
        if ( ! $request->has('website_id') || ! $request->has('old_php_version') || ! $request->has('new_php_version') ) {
            return redirect()->route('dashboard')->with('error', 'Invalid request...');
        }

        if ( $request->input('old_php_version') === $request->input('new_php_version') ) {
            return redirect()->route('dashboard')->with('error', 'No changes made...');
        }

        $websiteId = $request->input('website_id');
        $oldPhpVersion = $request->input('old_php_version');
        $newPhpVersion = $request->input('new_php_version');

        try{
            $this->appLogic->updatePhpVersion($websiteId, $oldPhpVersion, $newPhpVersion);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Failed to change PHP version');
        }

        return redirect()->route('dashboard')->with('success', 'PHP version changed successfully');
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
