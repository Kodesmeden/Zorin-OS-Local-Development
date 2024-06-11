<?php

namespace App\Logic;

use Illuminate\Support\Str;

class AppLogic
{
    protected $defaultPhpVersion;
    protected $thisPhpVersion;
    protected $phpVersions;
    protected $webserverUser;
    protected $websitePath = '/var/www/resources/websites';
    protected $sitesAvailable = '/etc/nginx/sites-available';
    protected $sitesEnabled = '/etc/nginx/sites-enabled';

    // TODO: Add variables for name, domain, databaseLogic etc.

    public function __construct()
    {
        $this->defaultPhpVersion = env('DEFAULT_PHP_VERSION');
        $this->thisPhpVersion = $this->defaultPhpVersion;
        $this->phpVersions = array_map('basename', glob('/etc/php/*'));
        $this->webserverUser = env('WEBSERVER_USER');

        // TODO: Add support for databaseLogic
        // TODO: Add support for website ID + set website info
    }

    private function getFormattedName($name) {
        return Str::headline($name);
    }

    private function getDomainFromName($name) {
        return Str::slug($name) . '.test';
    }

    public function createApp( $name, $type, $phpVersion = null, $repo = null) {
        $type = ucfirst($type);
        $callable = "create{$type}App";
        $this->thisPhpVersion = $phpVersion ?? $this->defaultPhpVersion;

        try{
            return $this->$callable($name, $repo);
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    private function createPhpApp($name) {
        $name = $this->getFormattedName($name);
        $domain = $this->getDomainFromName($name);

        try{
            $this->addSiteConf($domain);
            $this->addPhpConfig($domain);
            $this->addSymbolicLink($domain);
            $this->addWebsiteFolder($domain);
            $this->restartServices();
        } catch (\Exception $e) {
            throw $e;
        }

        return true;
    }

    private function createLaravelApp($name) {
        $name = $this->getFormattedName($name);
        $domain = $this->getDomainFromName($name);
    }

    private function createWordpressApp($name) {
        $name = $this->getFormattedName($name);
        $domain = $this->getDomainFromName($name);
    }

    private function createGitApp($name, $repo)
    {
        $name = $this->getFormattedName($name);
        $domain = $this->getDomainFromName($name);

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

        exec("cd {$this->websitePath}/ && git clone $gitUrl $domain 2>&1", $output, $errorCode);

        return (bool) empty($errorCode);
    }

    private function addSiteConf($domain) {
        $path = "/etc/nginx/sites-available/{$domain}";
        $content = 'server {
    listen 80;
    server_name ' . $domain . ';
    root /var/www/resources/websites/' . $domain . ';

    index index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php' . $this->thisPhpVersion . '-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* ^/(robots\.txt|favicon\.ico)$ {
        log_not_found off;
        access_log off;
    }

    location ~ /\.(git|well-known|vscode) {
        deny all;
    }
}';

        $this->createSystemFile($path, $content);
    }

    private function removeSiteConfig($websiteId, $php) {
        // $path = "/etc/nginx/sites-available/{$domain}";
    }

    private function addPhpConfig($domain) {
        $path = "/etc/php/{$this->thisPhpVersion}/fpm/pool.d/{$domain}.conf";
        $content = "[{$domain}]
user = {$this->webserverUser}
group = {$this->webserverUser}
listen = /run/php/php{$this->thisPhpVersion}.sock
listen.owner = {$this->webserverUser}
listen.group = {$this->webserverUser}
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35";

        $this->createSystemFile($path, $content);
    }

    private function removePhpConfig($websiteId, $php) {
        // $path = "/etc/php/{$this->thisPhpVersion}/fpm/pool.d/{$domain}.conf";
    }

    private function addSymbolicLink($domain) {
        $confPath = "/etc/nginx/sites-available/{$domain}";
        $symbolicLinkCmd = "ln -s {$confPath} /etc/nginx/sites-enabled/";

        return $this->runCommand($symbolicLinkCmd);
    }

    // private function removeSymbolicLink($domain) {
    //     $confPath = "etc/nginx/sites-enabled/{$domain}";
    //     $symbolicLinkCmd = "rm {$confPath}";

    //     return $this->runCommand($symbolicLinkCmd);
    // }

    private function addWebsiteFolder($domain) {
        $websitePath = "{$this->websitePath}/{$domain}";
        $createFolderCmd = "mkdir -p {$websitePath}";

        return $this->runCommand($createFolderCmd);
    }

    private function restartServices() {
        // TODO: Loop through all PHP versions and add to command
        $restartCmd = "service php{$this->thisPhpVersion}-fpm restart && service mysql restart && systemctl restart systemd-resolved && systemctl restart nginx && systemctl restart NetworkManager";

        return exec($restartCmd);
    }

    private function createSystemFile($path, $content){
        $error = '';
        $tempFile = tempnam(sys_get_temp_dir(), 'nginx_site_');
        file_put_contents($tempFile, $content);

        $command = "echo " . escapeshellarg(env('SUDO_PASSWORD')) . " | sudo -S cp " . escapeshellarg($tempFile) . " " . escapeshellarg($path);
        exec($command . " 2>&1", $output, $copyErrorCode);

        if ($copyErrorCode === 0) {
            $command = "echo " . escapeshellarg(env('SUDO_PASSWORD')) . " | sudo -S chmod 644 " . escapeshellarg($path);
            exec($command . " 2>&1", $output, $chmodErrorCode);

            if ($copyErrorCode !== 0) {
                $error = "Failed to set permissions. Return code: $chmodErrorCode\n";
            }
        } else {
            $error = "Failed to create file. Return code: $copyErrorCode\n";
        }
        
        unlink($tempFile);

        if (!empty($error)) {
            throw new \Exception($error);
        }

        return true;
    }

    private function runCommand($command) {
        $command = "echo " . escapeshellarg(env('SUDO_PASSWORD')) . " | sudo -S " . escapeshellarg($command);
        exec($command . " 2>&1", $output, $errorCode);

        return (bool) empty($errorCode);
    }

    public function updatePhpVersion($websiteId, $newPhpVersion){
        // TODO: Get old PHP version
    }
}