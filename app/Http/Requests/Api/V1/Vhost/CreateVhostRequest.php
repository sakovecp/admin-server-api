<?php

namespace App\Http\Requests\Api\V1\Vhost;

use Illuminate\Foundation\Http\FormRequest;

class CreateVhostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'domain' => ['required', 'string', 'unique:virtual_hosts,domain'],
            'port'   => ['nullable', 'integer', 'min:1', 'max:65535', 'unique:virtual_hosts,port,NULL,id,port,NOT_NULL'],
        ];
    }
}
