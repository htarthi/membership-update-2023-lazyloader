<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public static function handle($request, Closure $next)
    {
        $response = $next($request);
        // dd(Auth::user()->name);
        if (Auth::user()) {
            $response->headers->set('Content-Security-Policy', "frame-ancestors" . ' https://' . Auth::user()->name . ' https://admin.shopify.com');
        }

        return $response;
    }
}
