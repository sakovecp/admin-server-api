<?php
namespace App\Services\Servers\Nginx;

use App\Enumerations\ServerEnum;
use App\Services\Servers\AbstractServerManager;

class DockerNginxManager extends AbstractServerManager
{
    public function __construct(string $container = '')
    {
        parent::__construct($container);
    }

    protected function getDefaultServer(): string
    {
        return config('server.docker.container');
    }

    protected function runCommand(string $action): array
    {
        if (in_array($action, ['start', 'stop', 'restart'])) {
            return $this->run(['docker', $action, $this->server]);
        }

        if ($action === 'reload') {
            return $this->run(['docker', 'exec', $this->server, ServerEnum::SERVER_NGINX->value, '-s', 'reload']);
        }

        throw new \InvalidArgumentException("Unknown action: $action");
    }
}
