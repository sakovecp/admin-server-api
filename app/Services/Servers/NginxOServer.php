<?php
//namespace App\Services\Servers;
//
//use Symfony\Component\Process\Process;
//use Illuminate\Support\Facades\Log;
//
//class NginxManager implements ServerManagerInterface
//{
//    protected array $cfg;
//    protected string $container;
//
//    public function __construct(array $cfg = [])
//    {
//        $this->cfg = $cfg;
//        $this->container = $cfg['nginx_container'] ?? 'nginx';
//    }
//
//    protected function run(array $cmd, bool $throw = false): array
//    {
//        // cmd must be array for Process
//        $process = new Process($cmd);
//        $process->setTimeout(30);
//        $process->run();
//
//        $output = [
//            'success' => $process->isSuccessful(),
//            'exitCode' => $process->getExitCode(),
//            'output' => $process->getOutput(),
//            'errorOutput' => $process->getErrorOutput(),
//        ];
//
//        if ($throw && !$process->isSuccessful()) {
//            throw new \RuntimeException($process->getErrorOutput() ?: $process->getOutput());
//        }
//
//        Log::info('NginxManager::run', $output);
//        return $output;
//    }
//
//    protected function dockerExec(string $innerCmd): array
//    {
//        // uses docker exec if docker container is present
//        // safe building of command: pass as array elements
//        $cmd = ['docker', 'exec', $this->container];
//        // split innerCmd by space respecting shell? it's safer to pass ['/bin/sh', '-c', $innerCmd]
//        $cmd = array_merge($cmd, ['/bin/sh', '-c', $innerCmd]);
//        return $this->run($cmd);
//    }
//
//    public function start(): bool
//    {
//        // try docker start
//        $res = $this->run(['docker', 'start', $this->container]);
//        return $res['success'];
//    }
//
//    public function stop(): bool
//    {
//        $res = $this->run(['docker', 'stop', $this->container]);
//        return $res['success'];
//    }
//
//    public function restart(): bool
//    {
//        $res = $this->run(['docker', 'restart', $this->container]);
//        return $res['success'];
//    }
//
//    public function reload(): bool
//    {
//        // try graceful reload inside container; fall back to restart
//        $res = $this->dockerExec('nginx -s reload');
//        if ($res['success']) return true;
//        return $this->restart();
//    }
//
//    public function status(): array
//    {
//        return $this->run(['docker', 'ps', '--filter', 'name='.$this->container, '--format', '{{.ID}} {{.Status}}']);
//    }
//}
