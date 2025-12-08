<?php
namespace App\Http\Controllers\Api\V1\Server;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\Vhost\CreateVhostRequest;
use App\Http\Resources\Api\V1\VhostResource;
use App\Services\Vhost\VhostManagerInterface;
use Illuminate\Http\Request;

class VhostController extends ApiController
{
    protected $service;

    public function __construct(VhostManagerInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/vhosts",
     *     summary="Список усіх віртуальних хостів",
     *     tags={"Vhosts"},
     *     @OA\Response(
     *         response=200,
     *         description="Список віртуальних хостів",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="OK"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="domain", type="string", example="example.com"),
     *                     @OA\Property(property="port", type="integer", example=8081)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function all(Request $request)
    {
        return $this->success(VhostResource::collection($this->service->all()));
    }

    /**
     * @OA\Post(
     *     path="/vhosts",
     *     summary="Створити віртуальний хост",
     *     tags={"Vhosts"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"domain"},
     *             @OA\Property(property="domain", type="string", example="example.com"),
     *             @OA\Property(property="port", type="integer", nullable=true, example=8081)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Віртуальний хост створено"),
     *     @OA\Response(response=422, description="Помилка валідації")
     * )
     */
    public function create(CreateVhostRequest $request)
    {
        $result = $this->service->create($request->get('domain'), $request->get('port'));
        return $this->success($result);
    }

    /**
     * @OA\Delete(
     *     path="/vhosts/{domain}",
     *     summary="Видалити віртуальний хост",
     *     tags={"Vhosts"},
     *     @OA\Parameter(
     *         name="domain",
     *         in="path",
     *         required=true,
     *         description="Назва домену",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Віртуальний хост видалено"),
     *     @OA\Response(response=404, description="Віртуальний хост не знайдено")
     * )
     */
    public function delete(Request $request, string $domain)
    {
        $result = $this->service->delete($domain);
        return $this->success(['domain' => $domain , 'deleted' => $result]);
    }
}
