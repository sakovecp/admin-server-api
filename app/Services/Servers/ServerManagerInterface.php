<?php

namespace App\Services\Servers;

interface ServerManagerInterface {
    public function start():array;
    public function stop():array;
    public function restart():array;
    public function reload():array;
    public function getServerType():string;
}
