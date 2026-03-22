<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {

        if(!$request->user()){
            return redirect('login');
        }

        // check user role

        if(!$request->user()->hasAnyRole(...$roles)){
            abort(403, 'Unauthorized. Insufficient permissions.');
        }
        return $next($request);
    }
}
