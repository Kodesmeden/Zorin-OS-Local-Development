<?php

namespace App\Logic;

use Illuminate\Support\Str;

class InstallerLogic
{
    public function __construct()
    {
        
    }

    private function randomChar() {
        $min = 33;
        $max = 126;
    
        $chr = chr( mt_rand( $min, $max ) );
    
        if ( in_array( $chr, array( "'", "\"", "\\" ) ) ) {
            return $this->randomChar();
        }
    
        return $chr;
    }
    
    private function salt() {
        $salt = '';
    
        for ( $i = 0; $i < 64; $i++ ) {
            $salt .= $this->randomChar();
        }
    
        return $salt;
    }
    
    private function generateSalts() {
        $ak_salt = $this->salt();
        $sak_salt = $this->salt();
        $lik_salt = $this->salt();
        $nk_salt = $this->salt();
        $as_salt = $this->salt();
        $sas_salt = $this->salt();
        $lis_salt = $this->salt();
        $ns_salt = $this->salt();
    
        return [
            'AUTH_KEY'			=> $ak_salt,
            'SECURE_AUTH_KEY'	=> $sak_salt,
            'LOGGED_IN_KEY'		=> $lik_salt,
            'NONCE_KEY'			=> $nk_salt,
            'AUTH_SALT'			=> $as_salt,
            'SECURE_AUTH_SALT'	=> $sas_salt,
            'LOGGED_IN_SALT'	=> $lis_salt,
            'NONCE_SALT'		=> $ns_salt,
        ];
    }

    public function installPhp($path) {
        $domain = basename($path);
        $phpFile = $path . '/index.php';
        $phpContent = "<?php echo '{$domain} works!';";
        $command = "echo " . escapeshellarg($phpContent) . " | sudo tee " . escapeshellarg($phpFile);
        exec($command . " > /dev/null 2>&1", $output, $errorCode);

        return (bool) empty($errorCode);
    }

    public function installLaravel($path) {
        $domain = basename($path);

        exec("cd {$path}/ && composer create-project laravel/laravel . 2>&1", $output, $errorCode);

        return (bool) empty($errorCode);
    }

    public function installWordpress($path) {
        $domain = basename($path);
        $websitesPath = Str::replaceLast("/{$domain}", '', $path);

        try {
            copy( 'https://da.wordpress.org/latest-da_DK.tar.gz', $websitesPath . '/wordpress.tar.gz' );
            $phar = new \PharData( $websitesPath . '/wordpress.tar.gz' );
            $phar->extractTo( $websitesPath );
            rmdir($path);
            rename($websitesPath . '/wordpress', $path);

            $pluginDir = $path . '/wp-content/plugins';
            $wpConfigSample = $path . '/wp-config-sample.php';
            if ( file_exists( $wpConfigSample ) ) {
                $wpConfigSampleContent = file_get_contents( $wpConfigSample );

                $salts = $this->generateSalts();
                $auth_key = $salts['AUTH_KEY'];
                $secure_auth_key = $salts['SECURE_AUTH_KEY'];
                $logged_in_key = $salts['LOGGED_IN_KEY'];
                $nonce_key = $salts['NONCE_KEY'];
                $auth_salt = $salts['AUTH_SALT'];
                $secure_auth_salt = $salts['SECURE_AUTH_SALT'];
                $logged_in_salt = $salts['LOGGED_IN_SALT'];
                $nonce_salt = $salts['NONCE_SALT'];

                $wpConfigContent = str_replace( [
                    "define( 'DB_NAME', 'database_name_here' );",
                    "define( 'DB_USER', 'username_here' );",
                    "define( 'DB_PASSWORD', 'password_here' );",
                    "define( 'DB_HOST', 'localhost' );",
                    "define( 'DB_CHARSET', 'utf8' );",
                    "define( 'AUTH_KEY',         'put your unique phrase here' );",
                    "define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );",
                    "define( 'LOGGED_IN_KEY',    'put your unique phrase here' );",
                    "define( 'NONCE_KEY',        'put your unique phrase here' );",
                    "define( 'AUTH_SALT',        'put your unique phrase here' );",
                    "define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );",
                    "define( 'LOGGED_IN_SALT',   'put your unique phrase here' );",
                    "define( 'NONCE_SALT',       'put your unique phrase here' );",
                    "define( 'WP_DEBUG', false );",
                ], [
                    "define( 'DB_NAME', '$domain' );",
                    "define( 'DB_USER', 'root' );",
                    "define( 'DB_PASSWORD', '' );",
                    "define( 'DB_HOST', '127.0.0.1' );",
                    "define( 'DB_CHARSET', 'utf8mb4' );",
                    "define( 'AUTH_KEY',         '$auth_key' );",
                    "define( 'SECURE_AUTH_KEY',  '$secure_auth_key' );",
                    "define( 'LOGGED_IN_KEY',    '$logged_in_key' );",
                    "define( 'NONCE_KEY',        '$nonce_key' );",
                    "define( 'AUTH_SALT',        '$auth_salt' );",
                    "define( 'SECURE_AUTH_SALT', '$secure_auth_salt' );",
                    "define( 'LOGGED_IN_SALT',   '$logged_in_salt' );",
                    "define( 'NONCE_SALT',       '$nonce_salt' );",
                    "define( 'WP_DEBUG', true );",
                ], $wpConfigSampleContent );

                file_put_contents( $path . '/wp-config.php', $wpConfigContent );

                $wpPlugins = [
                    'akismet',
                    'ninja-forms',
                    'wordpress-importer',
                    'user-switching',
                ];

                foreach ( $wpPlugins as $pluginSlug ) {
                    $downloadFrom = "https://downloads.wordpress.org/plugin/{$pluginSlug}.latest-stable.zip";
                    $downloadTo = "{$pluginDir}/{$pluginSlug}.zip";
                    $pluginDir .= '/';

                    copy( $downloadFrom, $downloadTo );

                    $zip = new \ZipArchive( $downloadTo );
                    if ( $zip->open( $downloadTo ) ) {
                        $zip->extractTo( $pluginDir );
                        $zip->close();
                    }

                    unlink( $downloadTo );
                }
            }
            
            $themeDir = $path . '/wp-content/themes';
            $pluginDir = rtrim($pluginDir, '/');
            $removeFiles = [
                $websitesPath . '/wordpress.tar.gz',
                $path . '/license.txt',
                $path . '/readme.html',
                $themeDir . '/twentytwenty',
                $themeDir . '/twentytwentyone',
                $themeDir . '/twentytwentytwo',
                $themeDir . '/twentytwentythree',
                $pluginDir . '/hello.php'
            ];

            foreach ($removeFiles as $file) {
                if (!file_exists($file)) {
                    continue;
                }

                if (is_dir($file)) {
                    rmdir($file);
                } else {
                    unlink($file);
                }
            }

            return true;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    public function installGit($path, $repo)
    {
        if (substr($repo, -4) !== '.git') {
            return false;
        }

        if (strpos($repo, 'https://') === false) {
            if (strpos($repo, 'git@') === false) {
                return false;
            }

            $repo = str_replace([':', 'git@'], ['/', 'https://'], $repo);
        }

        $gitUser = env('GIT_USERNAME');
        $gitPass = env('GIT_TOKEN');

        $gitUrl = str_replace('https://', "https://$gitUser:$gitPass@", $repo);

        exec("cd {$path}/ && git clone {$gitUrl} . 2>&1", $output, $errorCode);

        return (bool) empty($errorCode);
    }
}