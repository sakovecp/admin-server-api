<?php

namespace App\Services\Docker;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\File;
use RuntimeException;

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
            throw new RuntimeException("docker-compose.yml not found at {$this->composePath}");
        }

        try {
            return Yaml::parse(File::get($this->composePath)) ?? [];
        } catch (ParseException $e) {
            throw new RuntimeException("Invalid YAML: " . $e->getMessage());
        }
    }

    protected function writeYaml(array $data): void
    {
        File::put($this->composePath, Yaml::dump($data, 10, 2));
    }

    protected function getService(array &$yaml, string $service): array
    {
        if (!isset($yaml['services'][$service])) {
            throw new RuntimeException("Service '$service' not found in docker-compose.yml");
        }

        return $yaml['services'][$service];
    }

    public function addPortToService(string $service, int $port): void
    {
        $this->updateServicePorts($service, $port, 'add');
    }

    public function removePortFromService(string $service, int $port): void
    {
        $this->updateServicePorts($service, $port, 'remove');
    }

    protected function updateServicePorts(string $service, int $port, string $action): void
    {
        $yaml = $this->readYaml();
        $serviceData = &$yaml['services'][$service] ?? null;

        if (!$serviceData) {
            throw new RuntimeException("Service '$service' not found");
        }

        $serviceData['ports'] ??= [];
        $portString = "$port:$port";

        if ($action === 'add' && !in_array($portString, $serviceData['ports'])) {
            $serviceData['ports'][] = $portString;
        }

        if ($action === 'remove') {
            $serviceData['ports'] = array_values(array_filter(
                $serviceData['ports'],
                fn($p) => $p !== $portString
            ));
            if (empty($serviceData['ports'])) {
                unset($serviceData['ports']);
            }
        }

        $this->writeYaml($yaml);
        $this->rebuildService($service);
    }

    public function rebuildService(string $service): void
    {
        $this->runCommand(["docker", "stop", $service]);
        $this->runCommand(["docker", "rm", "-v", $service]);
        $this->waitForContainerRemoval($service);
        $this->runCommand(["docker", "compose", "build", $service]);
        $this->runCommand(["docker", "compose", "up", "-d", $service]);
    }

    protected function waitForContainerRemoval(string $service, int $timeout = 10, int $interval = 1): void
    {
        while ($timeout > 0) {
            $existing = trim(shell_exec("docker ps -a --filter name={$service} --format '{{.Names}}'"));
            if (!$existing) break;
            sleep($interval);
            $timeout -= $interval;
        }
    }

    public function runCommand(array $cmd): void
    {
        $process = new Process($cmd, base_path());
        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                "Docker command failed: " . implode(' ', $cmd) . " " . $process->getErrorOutput()
            );
        }
    }
}
