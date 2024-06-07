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

        return $this->$callable($name, $repo);

        // Add site config
        // Add PHP config
        // Add symbolic link
        // Restart services
    }

    private function createPhpApp($name) {
        $name = $this->getFormattedName($name);
        $domain = $this->getDomainFromName($name);
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
    }

    private function removeSiteConfig($websiteId, $php) {
        // $path = "/etc/nginx/sites-available/{$domain}";
    }

    private function addPhpConfig($domain) {
        $path = "/etc/php/{$this->thisPhpVersion}/fpm/pool.d/{$domain}.conf";
        $content = '[dev.test]
user = kodesmeden
group = kodesmeden
listen = /run/php/php' . $this->thisPhpVersion . '.sock
listen.owner = kodesmeden
listen.group = kodesmeden
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35';
    }

    private function removePhpConfig($websiteId, $php) {
        // $path = "/etc/php/{$this->thisPhpVersion}/fpm/pool.d/{$domain}.conf";
    }

    private function addSymbolicLink($domain) {
        $confPath = "/etc/nginx/sites-available/{$domain}";

        $symbolicLink = "ln -s {$confPath} /etc/nginx/sites-enabled/";
    }

    private function removeSymbolicLink($domain) {
        $confPath = "etc/nginx/sites-enabled/{$domain}";

        $symbolicLink = "rm {$confPath}";
    }

    private function restartServices() {
        // TODO: Loop through all PHP versions and add to command
        $restart = "service php{$this->thisPhpVersion}-fpm restart && service mysql restart && systemctl restart systemd-resolved && systemctl restart nginx && systemctl restart NetworkManager";
    }



    public function updatePhpVersion($websiteId, $newPhpVersion){
        // TODO: Get old PHP version
    }
}