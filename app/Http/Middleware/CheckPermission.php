<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::user()) {
            return redirect('/');
        }
        $urlParts = explode('/', $request->path());
        $applicationName = $urlParts[0];
        $type = isset($urlParts[1]) ? $urlParts[1] : 'view';
        if ($applicationName != 'dashboard') {
            if (!Auth::user()->hasPermission($applicationName, $type)) {
                abort(403, 'Unauthorized action.');
            }
        }

        return $next($request);
    }
}
