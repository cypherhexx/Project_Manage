<?php

namespace App\Http\Middleware;

use Closure;

class EnsureFeatureIsEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $feature_name)
    {
       if($feature_name == 'support')
       {
            if(is_support_feature_disabled())
            {
                abort(404);
            }
       }
       elseif($feature_name =='customer_registration')
       {
        
            if(is_customer_registration_feature_disabled())
            {
                abort(404);
            }
       }
       else
       {
            abort(404);
       }
        return $next($request);
    }
}
