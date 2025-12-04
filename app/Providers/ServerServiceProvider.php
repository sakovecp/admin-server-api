<?php
namespace App\Providers;

use App\Enumerations\ServerEnum;
use App\Providers\Servers\NginxServiceProvider;
use App\Services\Host\HostManager;
use App\Services\Host\HostManagerInterface;
use Illuminate\Support\ServiceProvider;

class ServerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(HostManagerInterface::class, function ($app) {
            return new HostManager(config('server.portMinVal'), config('server.portMaxVal'));
        });

        if(config('server.type', 'nginx') === ServerEnum::SERVER_NGINX->value){
            $this->app->register(NginxServiceProvider::class);
        }
    }

    public function boot()
    {
        //
    }
}
