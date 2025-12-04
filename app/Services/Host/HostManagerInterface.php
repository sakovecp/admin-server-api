<?php
namespace App\Services\Host;
interface HostManagerInterface {
    public function all(): \Illuminate\Database\Eloquent\Collection;
    public function pickPort(): int;
    public function create(string $domain, int $port, string $confFile):\Illuminate\Database\Eloquent\Model;
    public function delete(string $domain): bool;
    public function isFreePort(int $port): bool;
    public function existDomain(string $domain): bool;
    public function find(string $domain):\Illuminate\Database\Eloquent\Model;
}
