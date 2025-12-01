<?php

namespace App\Services\Vhost;

interface VhostManagerInterface
{
    /**
     * @param string $domain
     * @param int|null $port
     * @return array {domain, port, path}
     */
    public function create(string $domain, ?int $port = null): array;

    /**
     * @param string $domain
     * @return bool
     */
    public function delete(string $domain): bool;
}
