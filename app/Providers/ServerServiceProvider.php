<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Server\ServerManagerInterface;
use App\Services\Server\NginxManager;
use App\Services\Vhost\VhostManagerInterface;
use App\Services\Vhost\NginxVhostManager;

class ServerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ServerManagerInterface::class, function ($app) {
            return new NginxManager(config('vhosts'));
        });

        $this->app->bind('server.nginx', function($app){
            return new NginxManager(config('vhosts'));
        });

        $this->app->bind(VhostManagerInterface::class, function($app){
            return new NginxVhostManager(config('vhosts'));
        });
        $this->app->bind('vhost.nginx', function($app){
            return new NginxVhostManager(config('vhosts'));
        });
    }

    public function boot()
    {
        //
    }
}
