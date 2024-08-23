<?php

namespace App\Logic;

class InstallerLogic
{
    public function __construct()
    {
        
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
        // TODO: Create and setup Laravel project
    }

    public function installWordpress($path) {
        // TODO: Create and setup Wordpress project
    }
}