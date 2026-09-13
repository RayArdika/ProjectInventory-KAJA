<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response
    {
        /*
        |--------------------------------------------------------------------------
        | CEK ROLE ADMIN
        |--------------------------------------------------------------------------
        */

        if (auth()->user()->role != 'admin') {

            return redirect('/produksi');

        }

        return $next($request);
    }
}
