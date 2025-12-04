<?php
//
//namespace App\Services\Servers;
//
//use App\Services\Docker\DockerComposeManager;
//use Illuminate\Support\Facades\File;
//use Exception;
//
//class NginxManager implements ServerManagerInterface
//{
//    protected string $vhostDir;
//    protected string $htmlDir;
//    protected DockerComposeManager $compose;
//
//    public function __construct()
//    {
//        $this->vhostDir = base_path('docker/nginx/conf.d');
//        $this->htmlDir = base_path('docker/nginx/html');
//        $this->compose = new DockerComposeManager();
//
//        File::ensureDirectoryExists($this->vhostDir);
//        File::ensureDirectoryExists($this->htmlDir);
//    }
//
//    public function start(): bool
//    {
//        $this->compose->runCommand(["docker", "compose", "start", "nginx"]);
//        return true;
//    }
//
//    public function stop(): bool
//    {
//        $this->compose->runCommand(["docker", "compose", "stop", "nginx"]);
//        return true;
//    }
//
//    public function restart(): bool
//    {
//        $this->compose->runCommand(["docker", "compose", "restart", "nginx"]);
//        return true;
//    }
//
//    public function reload(): bool
//    {
//        $this->restart();
//        return true;
//    }
//
//    public function status(): array
//    {
//        return [];
//    }
//
//    /**
//     * Create new virtual host for Nginx
//     */
//    public function createVirtualHost(string $domain, int $port): void
//    {
//        $vhostFile = "{$this->vhostDir}/{$domain}.conf";
//        $htmlFolder = "{$this->htmlDir}/{$domain}";
//
//        $config = <<<NGINX
//server {
//    listen $port;
//    server_name $domain;
//
//    root /var/www/html/$domain;
//    index index.html;
//
//    location / {
//        try_files \$uri \$uri/ =404;
//    }
//}
//NGINX;
//
//        // write config
//        File::put($vhostFile, $config);
//
//        // create HTML folder & index.html
//        File::ensureDirectoryExists($htmlFolder);
//        File::put("$htmlFolder/index.html", "HELLO $domain");
//
//        // Update Docker ports
//        $this->compose->addPortToService('nginx', $port);
//
//        $this->reload();
//    }
//
//    /**
//     * Remove virtual host
//     */
//    public function deleteVirtualHost(string $domain, int $port): void
//    {
//        File::delete("{$this->vhostDir}/{$domain}.conf");
//        File::deleteDirectory("{$this->htmlDir}/{$domain}");
//
//        $this->compose->removePortFromService('nginx', $port);
//
//        $this->reload();
//    }
//}
