<?php

namespace App\Logic;

use App\Models\Database;
use App\Models\Website;
use Illuminate\Support\Str;

class AppLogic
{
    protected $defaultPhpVersion;
    protected $thisPhpVersion;
    protected $phpVersions;
    protected $webserverUser;
    protected $webserverGroup;
    protected $websitePath = '/var/www/resources/websites';
    protected $sitesAvailable = '/etc/nginx/sites-available';
    protected $sitesEnabled = '/etc/nginx/sites-enabled';

    protected $appName;
    protected $appDomain;
    protected $phpSock;
    protected $database;
    protected $installer;

    // TODO: Add variables for name, domain, databaseLogic etc.

    public function __construct(DatabaseLogic $databaseLogic, InstallerLogic $installerLogic)
    {
        $this->defaultPhpVersion = env('DEFAULT_PHP_VERSION');
        $this->thisPhpVersion = $this->defaultPhpVersion;
        $this->phpVersions = array_map('basename', glob('/etc/php/*'));
        $this->webserverUser = env('WEBSERVER_USER');
        $this->webserverGroup = env('WEBSERVER_GROUP');
        $this->database = $databaseLogic;
        $this->installer = $installerLogic;

        // TODO: Add support for databaseLogic
        // TODO: Add support for installerLogic
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
        $install = "install{$type}";
        $this->thisPhpVersion = $phpVersion ?? $this->defaultPhpVersion;
        $this->appName = $this->getFormattedName($name);
        $this->appDomain = $this->getDomainFromName($this->appName);

        $existingSite = Website::where('domain', $this->appDomain)->first();
        if ($existingSite) {
            // TODO: Add notification warning?
            $this->thisPhpVersion = $existingSite->php;
        }

        $phpSockName = Str::slug($name, '');
        $this->phpSock = "/run/php/php{$this->thisPhpVersion}-{$phpSockName}.sock";

        try{
            $this->addSiteConf($this->appDomain);
            $this->addPhpConfig($this->appDomain);
            $this->addSymbolicLink($this->appDomain);
            $this->addWebsiteFolder($this->appDomain);
            $this->installer->$install("{$this->websitePath}/{$this->appDomain}");
            $this->database->createDatabase($this->appDomain);
            $this->resetPermissions($this->appDomain);
            $this->updateHostsFile();
            
            Website::updateOrCreate([
                'domain' => $this->appDomain,
            ], [
                'name' => $this->appName,
                'repo' => $repo,
                'type' => $type,
                'php' => $this->thisPhpVersion,
            ]);

            // TODO: Add database creation here

            $this->restartServices();
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    private function createGitApp($repo)
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

        exec("cd {$this->websitePath}/ && git clone {$gitUrl} {$this->appDomain} 2>&1", $output, $errorCode);

        return (bool) empty($errorCode);
    }

    private function addSiteConf($domain, $laravel = false) {
        $path = "/etc/nginx/sites-available/{$domain}";
        $root = $laravel ? "/var/www/resources/websites/{$domain}/public" : "/var/www/resources/websites/{$domain}";

        $content = 'server {
    listen 80;
    server_name ' . $domain . ';
    root ' . $root . ';

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

    private function removeSiteConfig($websiteId) {
        // $path = "/etc/nginx/sites-available/{$domain}";
        // $path = "/etc/nginx/sites-enabled/{$domain}";
    }

    private function addPhpConfig($domain) {
        $name = Str::replace('.test', '', $domain);
        $path = "/etc/php/{$this->thisPhpVersion}/fpm/pool.d/{$domain}.conf";
        $content = "[{$domain}]
user = {$this->webserverUser}
group = {$this->webserverGroup}
listen = {$this->phpSock}
listen.owner = {$this->webserverUser}
listen.group = {$this->webserverGroup}
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
        $confPath = escapeshellarg("/etc/nginx/sites-available/{$domain}");
        $symbolicLinkCmd = "ln -s {$confPath} /etc/nginx/sites-enabled/";

        return $this->runCommand($symbolicLinkCmd);
    }

    // private function removeSymbolicLink($domain) {
    //     $confPath = "etc/nginx/sites-enabled/{$domain}";
    //     $symbolicLinkCmd = "rm {$confPath}";

    //     return $this->runCommand($symbolicLinkCmd);
    // }

    private function addWebsiteFolder($domain) {
        $websitePath = escapeshellarg("{$this->websitePath}/{$domain}");
        $createFolderCmd = "mkdir -p {$websitePath}";

        // return $createFolderCmd;
        return $this->runCommand($createFolderCmd);
    }

    private function resetPermissions() {
        $command = [
            "chown -R {$this->webserverUser}:{$this->webserverGroup} {$this->websitePath}",
            "find {$this->websitePath} -type d -exec chmod 755 {} \;",
            "find {$this->websitePath} -type f -exec chmod 644 {} \;",
        ];
        $errorCode = $this->runCommand($command);

        if (!empty($errorCode)) {
            throw new \Exception("Failed to set permissions. Return code: $errorCode\n");
        }

        return true;
    }

    public function updateHostsFile() {
        $hostsFile = '/etc/hosts';

        $hostsContent  = "127.0.0.1 localhost\n";
        $hostsContent .= "127.0.0.1 pma.test\n";
        $hostsContent .= "127.0.0.1 dev.test\n";

        $websites = array_map('basename', glob("{$this->websitePath}/*"));

        foreach ($websites as $website) {
            $hostsContent .= "127.0.0.1 {$website}\n";
        }

        $command = "echo " . escapeshellarg($hostsContent) . " | sudo tee " . escapeshellarg($hostsFile);
        exec($command . " 2>&1", $output, $errorCode);

        if ($errorCode !== 0) {
            throw new \Exception("Failed to update hosts file. Return code: $errorCode\n");
        }

        return true;
    }

    public function restartServices() {
        $restartCmd = [
            "systemctl reload php{$this->thisPhpVersion}-fpm",
        ];

        foreach($this->phpVersions as $version) {
            if ($version !== $this->thisPhpVersion) {
                $restartCmd[] = "systemctl reload php{$version}-fpm";
            }
        }

        $restartCmd[] = "systemctl reload systemd-resolved";
        $restartCmd[] = "systemctl reload nginx";

        return $this->runCommand($restartCmd);
    }

    private function createSystemFile($path, $content){
        $error = '';
        $tempFile = tempnam(sys_get_temp_dir(), 'nginx_site_');
        file_put_contents($tempFile, $content);

        $command = "cp " . escapeshellarg($tempFile) . " " . escapeshellarg($path);
        $copyErrorCode = $this->runCommand($command);

        if ($copyErrorCode === 0) {
            $command = "chmod 644 " . escapeshellarg($path);
            $chmodErrorCode = $this->runCommand($command);

            if ($chmodErrorCode !== 0) {
                $error = "Failed to set permissions. Return code: $chmodErrorCode";
            }
        } else {
            $error = "Failed to create file ({$path}). Return code: {$copyErrorCode}. Command: {$command}";
        }
        
        unlink($tempFile);

        if (!empty($error)) {
            throw new \Exception($error);
        }

        return true;
    }

    private function runCommand($command) {
        if (is_array($command)) {
            $command = implode(' && sudo ', $command);
        }
        $command = "sudo " . $command;

        exec($command . " 2>&1", $output, $errorCode);

        return $errorCode;
    }

    public function updatePhpVersion($websiteId, $newPhpVersion){
        // TODO: Get old PHP version
    }
}