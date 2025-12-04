<?php
namespace App\Services\Servers\Nginx;

use App\Enumerations\PlatformEnum;
use App\Enumerations\ServerEnum;
use App\Services\Servers\AbstractServerManager;

class LocalNginxManager extends AbstractServerManager
{
    protected PlatformEnum $platform;

    public function __construct(string $server = '')
    {
        parent::__construct($server);
        $this->platform = $this->detectPlatform();
    }

    protected function getDefaultServer(): string
    {
        return config('server.local.binary', '/usr/sbin/nginx');
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
        switch ($this->platform) {
            case PlatformEnum::WINDOWS:
                return $this->run([$this->server, "-s", $action]);

            case PlatformEnum::MAC:
                if (in_array($action, ['start', 'stop', 'restart'])) {
                    return $this->run(['brew', 'services', $action, ServerEnum::SERVER_NGINX->value]);
                }
                return $this->run([$this->server, '-s', $action]);

            case PlatformEnum::LINUX:
            default:
                $systemctl = ['systemctl', $action, ServerEnum::SERVER_NGINX->value];
                $service = ['service', ServerEnum::SERVER_NGINX->value, $action];

                if (is_executable('/bin/systemctl') || is_executable('/usr/bin/systemctl')) {
                    return $this->run($systemctl);
                }

                return $this->run($service);
        }
    }
}
