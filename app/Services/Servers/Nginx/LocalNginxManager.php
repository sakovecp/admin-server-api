<?php
namespace App\Services\Servers\Nginx;

use App\Enumerations\PlatformEnum;
use App\Enumerations\ServerEnum;
use App\Services\Servers\AbstractServerManager;

class LocalNginxManager extends AbstractServerManager
{
    protected PlatformEnum $platform;

    public function __construct(string $server)
    {
        parent::__construct($server);
        $this->platform = $this->detectPlatform();
    }

    public function getServerType(): string
    {
        return ServerEnum::SERVER_NGINX->value;
    }

    /**
     * Автоматичне визначення платформи
     */
    protected function detectPlatform(): PlatformEnum
    {
        if (str_starts_with(PHP_OS_FAMILY, 'Windows')) {
            return PlatformEnum::WINDOWS;
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            return PlatformEnum::MAC;
        }

        return PlatformEnum::LINUX;
    }

    protected function runCommand(string $action): array
    {
        return match ($this->platform) {
            PlatformEnum::WINDOWS => $this->runWindows($action),
            PlatformEnum::MAC     => $this->runMac($action),
            PlatformEnum::LINUX   => $this->runLinux($action),
        };
    }

    protected function runWindows(string $action): array
    {
        return $this->run([$this->server, "-s", $action]);
    }

    protected function runMac(string $action): array
    {
        $nginx = ServerEnum::SERVER_NGINX->value;

        if (in_array($action, ['start', 'stop', 'restart'], true)) {
            return $this->run(['brew', 'services', $action, $nginx]);
        }

        return $this->run([$this->server, '-s', $action]);
    }

    protected function runLinux(string $action): array
    {
        if ($action === 'start') {
            return $this->run([$this->server]);
        }

        if ($action === 'restart') {
            $this->run([$this->server, "-s", "stop"]);
            usleep(500_000);
            return $this->run([$this->server]);
        }

        return $this->run([$this->server, "-s", $action]);
    }
}
