<?php
namespace App\Services\Docker;
interface DockerComposeInterface {
    public function addPortToService(string $service, int $port): void;
    public function removePortFromService(string $service, int $port): void;
    public function rebuildService(string $service): void;
    public function runCommand(array $cmd): void;
}
