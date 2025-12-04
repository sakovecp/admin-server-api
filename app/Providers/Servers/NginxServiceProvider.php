<?php
namespace App\Providers\Servers;

use App\Services\Docker\DockerComposeManager;
use App\Services\Host\HostManagerInterface;
use App\Services\Servers\Nginx\DockerNginxManager;
use App\Services\Servers\Nginx\LocalNginxManager;
use App\Services\Vhost\VhostManager;
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
            return new VhostManager(
                config('server.local.configDir'),
                config('server.local.htmlDir'),
                config('server.templates.conf'),
                config('server.templates.vhost_index'),
                $this->app->get(ServerManagerInterface::class),
                $this->app->get(HostManagerInterface::class),
                new DockerComposeManager(),
                new Filesystem()
            );
        });
    }

    public function boot()
    {
        //
    }
}
