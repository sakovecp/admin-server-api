<?php

namespace App\Services\Host;

use App\Models\VirtualHost;

class HostManager implements HostManagerInterface
{
    protected int $minValPort;
    protected int $maxValPort;

    public function __construct(int $minValPort = 8081, int $maxValPort = 30000)
    {
        $this->minValPort = $minValPort;
        $this->maxValPort = $maxValPort;
    }

    public function pickPort(): int
    {
        for ($p = $this->minValPort; $p <= $this->maxValPort; $p++) {
            if (!VirtualHost::where('port', $p)->exists()) return $p;
        }
        throw new \RuntimeException('No free ports available in range');
    }

    public function create(string $domain, int $port, string $confFile): \Illuminate\Database\Eloquent\Model
    {
        return VirtualHost::create(['domain' => $domain, 'port' => $port, 'conf_file' => $confFile]);
    }

    public function find(string $domain): \Illuminate\Database\Eloquent\Model
    {
        return VirtualHost::findOrFail(['domain' => $domain]);
    }

    public function delete(string $domain): bool
    {
        $vh = VirtualHost::findOrFail(['domain' => $domain]);
        $vh->delete();
        return true;
    }

    /**
     * @return VirtualHost[]
     */
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return VirtualHost::get();
    }

    public function isFreePort(int $port): bool
    {
        return !VirtualHost::where('port', $port)->exists();
    }

    public function existDomain(string $domain): bool
    {
        return VirtualHost::where('domain', $domain)->exists();
    }
}
