<?php

namespace App\Services\Docker;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;
use Exception;

class DockerComposeManager implements DockerComposeInterface
{
    protected string $composePath;

    public function __construct(?string $composePath = null)
    {
        $this->composePath = $composePath ?? base_path('docker-compose.yml');
    }

    protected function readYaml(): array
    {
        if (!File::exists($this->composePath)) {
            throw new Exception("docker-compose.yml not found at {$this->composePath}");
        }

        try {
            return Yaml::parse(File::get($this->composePath)) ?? [];
        } catch (ParseException $e) {
            throw new Exception("Invalid YAML: " . $e->getMessage());
        }
    }

    protected function writeYaml(array $data): void
    {
        File::put($this->composePath, Yaml::dump($data, 10, 2));
    }

    public function addPortToService(string $service, int $port): void
    {
        $yaml = $this->readYaml();

        if (!isset($yaml['services'][$service])) {
            throw new Exception("Service '$service' not found in docker-compose.yml");
        }

        $yaml['services'][$service]['ports'] ??= [];

        $portString = "$port:$port";

        if (!in_array($portString, $yaml['services'][$service]['ports'])) {
            $yaml['services'][$service]['ports'][] = $portString;
            $this->writeYaml($yaml);
            $this->rebuildService($service);
        }
    }

    public function removePortFromService(string $service, int $port): void
    {
        $yaml = $this->readYaml();

        if (!isset($yaml['services'][$service])) {
            throw new Exception("Service '$service' not found");
        }

        $portString = "$port:$port";

        $yaml['services'][$service]['ports'] = array_values(array_filter(
            $yaml['services'][$service]['ports'] ?? [],
            fn($p) => $p !== $portString
        ));

        $this->writeYaml($yaml);
        $this->rebuildService($service);
    }

    public function rebuildService(string $service): void
    {
        $this->runCommand(["docker", "compose", "up", "-d", $service]);
    }

    public function runCommand(array $cmd): void
    {
        $process = new Process($cmd);
        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception("Docker command failed: " . $process->getErrorOutput());
        }
    }
}
