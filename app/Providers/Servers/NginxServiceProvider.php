<?php
namespace App\Providers\Servers;

use App\Services\Docker\DockerComposeInterface;
use App\Services\Host\HostManagerInterface;
use App\Services\Servers\Nginx\DockerNginxManager;
use App\Services\Servers\Nginx\LocalNginxManager;
use App\Services\Vhost\BladeVhostTemplateRenderer;
use App\Services\Vhost\DockerVhostManager;
use App\Services\Vhost\LocalVhostManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use App\Services\Servers\ServerManagerInterface;
use App\Services\Vhost\VhostManagerInterface;

class NginxServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ServerManagerInterface::class, function ($app) {
            if(config('server.mode') === 'docker'){
                return new DockerNginxManager(config('server.docker.container'));
            }

            return new LocalNginxManager(config('server.local.binary'));
        });

        $this->app->bind(VhostManagerInterface::class, function ($app) {
            $renderer = new BladeVhostTemplateRenderer(config('server.templates.conf'), config('server.templates.vhost_index'));
            $fs = new Filesystem();
            if(config('server.mode') === 'docker'){
                return new DockerVhostManager(
                    config('server.local.configDir'),
                    config('server.local.htmlDir'),
                    $this->app->get(ServerManagerInterface::class),
                    $this->app->get(HostManagerInterface::class),
                    $fs,
                    $renderer,
                    $this->app->get(DockerComposeInterface::class),
                );
            }

            return new LocalVhostManager(
                config('server.local.configDir'),
                config('server.local.htmlDir'),
                $this->app->get(ServerManagerInterface::class),
                $this->app->get(HostManagerInterface::class),
                $fs,
                $renderer
            );
        });
    }

    public function boot()
    {
        //
    }
}
