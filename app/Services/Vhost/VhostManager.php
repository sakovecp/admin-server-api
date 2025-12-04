<?php

namespace App\Services\Vhost;


use App\Services\Docker\DockerComposeManager;
use App\Services\Host\HostManagerInterface;
use App\Services\Servers\ServerManagerInterface;
use Illuminate\Filesystem\Filesystem;


class VhostManager implements VhostManagerInterface
{
    protected Filesystem $fs;
    protected HostManagerInterface $host;
    protected ServerManagerInterface $server;
    protected DockerComposeManager $compose;
    protected $configDir;
    protected $htmlDir;
    protected $templateConfig;
    protected $templateHtml;


    public function __construct(string $configDir, string $htmlDir, string $templateConfig, string $templateHtml, ServerManagerInterface $server, HostManagerInterface $host, DockerComposeManager $compose, Filesystem $fs)
    {
        $this->fs = $fs;
        $this->server = $server;
        $this->host = $host;
        $this->compose = $compose;
        $this->configDir = $configDir;
        $this->htmlDir = $htmlDir;
        $this->templateConfig = $templateConfig;
        $this->templateHtml = $templateHtml;
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->host->all();
    }

    public function create(string $domain, ?int $port = null):array
    {
        $domain = $this->sanitizeDomain($domain);
        if($port && !$this->host->isFreePort($port)){
            throw new \RuntimeException('Port is busy');
        }

        if($this->host->existDomain($domain)){
            throw new \RuntimeException('Domain already exists');
        }

        if($port === null){
            $port = $this->host->pickPort();
        }

        $confFile = $this->createConfig($domain, $port);
        $this->createIndexPage($domain);

        if($this->host->create($domain, $port, $confFile)){
            $this->server->reload();
            return ['domain' => $domain, 'port' => $port];
        }

        return throw new \RuntimeException('Error creating vhost');
    }

    public function delete(string $domain):bool
    {
        $domain = $this->sanitizeDomain($domain);
        if($this->host->existDomain($domain)){
            $this->deleteConfig($domain);
            $this->deleteIndexPage($domain);
            $this->host->delete($domain);
            $this->server->reload();
            return true;
        }
        return false;
    }

    protected function sanitizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        if (!preg_match('/^[a-z0-9\-\.]+$/', $domain)) {
            throw new \InvalidArgumentException('Invalid domain name');
        }
        return $domain;
    }

    protected function createConfig(string $domain, int $port):string
    {
        $this->fs->ensureDirectoryExists($this->configDir);
        $filePath= "{$this->configDir}/{$domain}.conf";
        $config = $this->renderConfig(['domain' => $domain, 'port' => $port]);
        if($this->fs->put($filePath, $config)){
            return $filePath;
        }

        throw new \RuntimeException('Error writing configuration file');
    }

    protected function deleteConfig(string $domain):bool
    {
        $filePath= "{$this->configDir}/{$domain}.conf";
        if($this->fs->exists($filePath)){
            $this->fs->delete($filePath);
            return true;
        }
        return false;
    }

    protected function createIndexPage(string $domain):bool
    {
        $path = "$this->htmlDir/$domain";
        $this->fs->ensureDirectoryExists($path);
        $html = $this->renderHtml(['domain' => $domain]);

        if($this->fs->put("$path/index.html", $html)){
            return true;
        }

        throw new \RuntimeException('Error writing index.html file');
    }

    protected function deleteIndexPage(string $domain):bool
    {
        $path = "$this->htmlDir/$domain";
        if($this->fs->deleteDirectory($path)){
            return true;
        }
        return false;
    }

    protected function renderConfig(array $data): string
    {
        return view($this->templateConfig, $data)->render();
    }

    protected function renderHtml(array $data): string
    {
        return view($this->templateHtml, $data)->render();
    }
}
