<?php
namespace App\Services\Vhost;

interface VhostManagerInterface
{
    /**
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function all(): \Illuminate\Database\Eloquent\Collection;

    /**
     * @param string $domain
     * @param int|null $port
     * @return array {domain, port, conf_path}
     */
    public function create(string $domain, ?int $port = null): array;

    /**
     * @param string $domain
     * @return bool
     */
    public function delete(string $domain): bool;
}
