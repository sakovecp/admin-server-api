<?php
//
//namespace App\Services\Servers;
//
//use App\Services\Docker\DockerComposeManager;
//use App\Services\Servers\ServerManagerInterface;
//use Exception;
//
//
//abstract class ServerOldManager implements ServerManagerInterface
//{
//    protected string $vhostDir;
//    protected string $htmlDir;
//    protected DockerComposeManager $compose;
//
//
//    public function __construct()
//    {
//        $this->vhostDir = base_path('docker/apache/vhosts');
//        $this->htmlDir = base_path('docker/apache/html');
//        $this->compose = new DockerComposeManager();
//    }
//
//
//    public function start(): void
//    {
//        $this->compose->runCommand(["docker", "compose", "start", "apache"]);
//    }
//
//
//    public function stop(): void
//    {
//        $this->compose->runCommand(["docker", "compose", "stop", "apache"]);
//    }
//
//
//    public function restart(): void
//    {
//        $this->compose->runCommand(["docker", "compose", "restart", "apache"]);
//    }
//
//
//    public function reload(): void
//    {
//        $this->restart(); // Apache reload inside docker is same as restart
//    }
//
//
//    public function createVirtualHost(string $domain, int $port): void
//    {
//        $vhostFile = "$this->vhostDir/$domain.conf";
//        $htmlFile = "$this->htmlDir/$domain.html";
//
//
//        $vhost = "<VirtualHost *:$port>\n" .
//            " ServerName $domain\n" .
//            " DocumentRoot /var/www/html/$domain\n" .
//            " <Directory /var/www/html/$domain>\n" .
//            " AllowOverride All\n" .
//            " Require all granted\n" .
//            " </Directory>\n" .
//            "</VirtualHost>\n";
//
//
//        file_put_contents($vhostFile, $vhost);
//        file_put_contents($htmlFile, "HELLO $domain");
//
//
//// Create folder inside docker mount
//        if (!is_dir($this->htmlDir . "/$domain")) {
//            mkdir($this->htmlDir . "/$domain", 0777, true);
//        }
//        file_put_contents($this->htmlDir . "/$domain/index.html", "HELLO $domain");
//
//
//// Update ports
//        $this->compose->addPortToService('apache', $port);
//
//
//        $this->reload();
//    }
//
//
//    public function deleteVirtualHost(string $domain, int $port): void
//    {
//        @unlink($this->vhostDir . "/$domain.conf");
//        @unlink($this->htmlDir . "/$domain/index.html");
//
//
//        $this->compose->removePortFromService('apache', $port);
//        $this->reload();
//    }
//}
