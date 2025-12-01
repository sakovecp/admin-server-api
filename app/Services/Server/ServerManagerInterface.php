<?php

namespace App\Services\Server;

interface ServerManagerInterface
{
    /**
     * @return bool
     */
    public function start(): bool;

    /**
     * @return bool
     */
    public function stop(): bool;

    /**
     * @return bool
     */
    public function restart(): bool;

    /**
     * @return bool
     */
    public function reload(): bool;

    /**
     * @return array
     */
    public function status(): array;
}
