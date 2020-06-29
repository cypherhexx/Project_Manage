<?php

namespace App\Http\Middleware;

use Closure;
use App\Setting;

class SettingsForCustomerPanel
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
        $default_language = auth()->user()->customer->default_language;
        
        if($default_language)
        {
            app()->setLocale($default_language);
        }
        return $next($request);
    }
}
