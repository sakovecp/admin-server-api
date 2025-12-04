<?php

namespace App\Services\Vhost;

use App\Services\Docker\DockerComposeManager;
use App\Services\Host\HostManagerInterface;
use App\Services\Servers\ServerManagerInterface;
use Illuminate\Filesystem\Filesystem;

class DockerVhostManager extends AbstractVhostManager
{
    protected DockerComposeManager $compose;
    public function __construct(
        string $configDir,
        string $htmlDir,
        ServerManagerInterface $server,
        HostManagerInterface $host,
        Filesystem $fs,
        VhostTemplateRendererInterface $renderer,
        DockerComposeManager $compose,
    )
    {
        $this->compose = $compose;
        parent::__construct($configDir, $htmlDir, $server, $host, $fs, $renderer);
    }

    public function create(string $domain, ?int $port = null): array
    {
        $domain = $this->sanitizeDomain($domain);

        if ($port && !$this->host->isFreePort($port)) {
            throw new \RuntimeException('Port is busy');
        }

        if ($this->host->existDomain($domain)) {
            throw new \RuntimeException('Domain already exists');
        }

        $port = $this->host->pickPort();
        $confFile = $this->createConfig($domain, $port);
        $this->createIndexPage($domain);

        if ($this->host->create($domain, $port, $confFile)) {
            $this->compose->addPortToService($this->server->getServerType(), $port);
            $this->server->reload();
            return ['domain' => $domain, 'port' => $port];
        }

        throw new \RuntimeException('Error creating vhost');
    }

    public function delete(string $domain): bool
    {
        $domain = $this->sanitizeDomain($domain);
        $host = $this->host->find($domain);

        $this->deleteConfig($domain);
        $this->deleteIndexPage($domain);
        $this->compose->removePortFromService($this->server->getServerType(), $host->port);
        $host->delete();
        $this->server->reload();

        return true;
    }
}
