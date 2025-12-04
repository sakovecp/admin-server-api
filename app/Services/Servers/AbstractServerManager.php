<?php
namespace App\Services\Servers;

use Symfony\Component\Process\Process;

abstract class AbstractServerManager implements ServerManagerInterface
{
    protected string $server;

    public function __construct(string $server = '')
    {
        $this->server = $server ?: $this->getDefaultServer();
    }

    /**
     * Повертає дефолтну команду для nginx
     */
    abstract protected function getDefaultServer(): string;

    /**
     * Повертає дефолтну команду для nginx
     */
    abstract public function getServerType(): string;

    /**
     * Виконати команду
     */
    protected function run(array $cmd): array
    {
        $process = new Process($cmd);
        $process->setTimeout(60);
        $process->run();

        return [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput(),
            'error' => $process->getErrorOutput(),
            'cmd' => $cmd,
        ];
    }

    public function start(): array
    {
        return $this->runCommand('start');
    }

    public function stop(): array
    {
        return $this->runCommand('stop');
    }

    public function restart(): array
    {
        return $this->runCommand('restart');
    }

    public function reload(): array
    {
        return $this->runCommand('reload');
    }

    /**
     * Виконати платформозалежну команду
     */
    abstract protected function runCommand(string $action): array;
}
