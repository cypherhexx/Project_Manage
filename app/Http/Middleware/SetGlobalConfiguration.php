<?php

namespace App\Http\Middleware;

use Closure;
use App\Setting;

class SetGlobalConfiguration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
         // Loading the Application Based configurations
        Setting::setup_app_config();
        return $next($request);
    }
}
