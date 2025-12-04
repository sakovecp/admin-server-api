<?php
namespace App\Http\Controllers\Api\V1\Server;

use App\Http\Controllers\Api\V1\ApiController;
use App\Services\Servers\ServerManagerInterface;
use Illuminate\Http\Request;

class ServerController extends ApiController
{
    protected $server;

    public function __construct(ServerManagerInterface $server)
    {
        $this->server = $server;
    }

    /**
     * @OA\Post(
     *     path="/server/start",
     *     summary="Запустити сервер",
     *     tags={"Server"},
     *     @OA\Response(response=200, description="Сервер запущено")
     * )
     */
    public function start(Request $request)
    {
        return response()->json($this->server->start());
    }

    /**
     * @OA\Post(
     *     path="/server/stop",
     *     summary="Зупинити сервер",
     *     tags={"Server"},
     *     @OA\Response(response=200, description="Сервер зупинено")
     * )
     */
    public function stop(Request $request)
    {
        return response()->json($this->server->stop());
    }

    /**
     * @OA\Post(
     *     path="/server/restart",
     *     summary="Пререзавантажити сервер",
     *     tags={"Server"},
     *     @OA\Response(response=200, description="Сервер перезавантажено")
     * )
     */
    public function restart(Request $request)
    {
        return response()->json($this->server->restart());
    }

    /**
     * @OA\Post(
     *     path="/server/reload",
     *     summary="Перезавантажити конфігурацію сервера",
     *     tags={"Server"},
     *     @OA\Response(response=200, description="Сервер перезавантажено")
     * )
     */
    public function reload(Request $request)
    {
        return response()->json($this->server->reload());
    }
}
