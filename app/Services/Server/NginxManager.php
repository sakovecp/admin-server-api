<?php

namespace App\Services\Server;

use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class NginxManager implements ServerManagerInterface
{
    protected array $config;
    protected string $container;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->container = $config['nginx_container'] ?? 'nginx';
    }

    /**
     * @param array $cmd
     * @param bool $throw
     * @return array
     */
    protected function run(array $cmd, bool $throw = false): array
    {
        $process = new Process($cmd);
        $process->setTimeout(30);
        $process->run();

        $output = [
            'success' => $process->isSuccessful(),
            'exitCode' => $process->getExitCode(),
            'output' => $process->getOutput(),
            'errorOutput' => $process->getErrorOutput(),
        ];

        if ($throw && !$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput() ?: $process->getOutput());
        }

        Log::info('NginxManager::run', $output);
        return $output;
    }

    /**
     * @param string $innerCmd
     * @return array
     */
    protected function dockerExec(string $innerCmd): array
    {
        $cmd = ['docker', 'exec', $this->container];
        $cmd = array_merge($cmd, ['/bin/sh', '-c', $innerCmd]);
        return $this->run($cmd);
    }

    /**
     * @return bool
     */
    public function start(): bool
    {
        $res = $this->run(['docker', 'start', $this->container]);
        return $res['success'];
    }

    /**
     * @return bool
     */
    public function stop(): bool
    {
        $res = $this->run(['docker', 'stop', $this->container]);
        return $res['success'];
    }

    /**
     * @return bool
     */
    public function restart(): bool
    {
        $res = $this->run(['docker', 'restart', $this->container]);
        return $res['success'];
    }

    /**
     * @return bool
     */
    public function reload(): bool
    {
        $res = $this->dockerExec('nginx -s reload');
        if ($res['success']) return true;
        return $this->restart();
    }

    /**
     * @return array
     */
    public function status(): array
    {
        return $this->run(['docker', 'ps', '--filter', 'name=' . $this->container, '--format', '{{.ID}} {{.Status}}']);
    }
}
