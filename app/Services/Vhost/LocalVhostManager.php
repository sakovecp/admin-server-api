<?php

namespace App\Services\Vhost;

class LocalVhostManager extends AbstractVhostManager
{
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
            $this->server->reload();
            return ['domain' => $domain, 'port' => $port];
        }

        throw new \RuntimeException('Error creating vhost');
    }

    public function delete(string $domain): bool
    {
        $domain = $this->sanitizeDomain($domain);
        if (!$this->host->existDomain($domain)) {
            return false;
        }

        $this->deleteConfig($domain);
        $this->deleteIndexPage($domain);
        $this->host->delete($domain);
        $this->server->reload();

        return true;
    }
}
