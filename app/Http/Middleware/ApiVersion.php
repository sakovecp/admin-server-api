<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiVersion
{
    public function handle(Request $request, Closure $next)
    {
        //Версія з Accept Header
        //Accept: application/json; version=2
        $accept = $request->header('Accept');

        preg_match('/version=(\d+)/', $accept, $matches);
        if (!empty($matches[1])) {
            $request->attributes->set('api_version', 'v' . $matches[1]);
            return $next($request);
        }

        //Версія з URL /api/v1/*
        if ($request->segment(2) === 'v1') {
            $request->attributes->set('api_version', $request->segment(2));
            return $next($request);
        }

        //За замовчуванням v1
        $request->attributes->set('api_version', 'v1');

        return $next($request);
    }
}
