<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class VhostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'domain'  => $this->domain,
            'port'  => $this->port,
        ];
    }
}
