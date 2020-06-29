<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $base_url               = url('/');
        $previous_url           = url()->previous();
        $is_internal_request    = false;

        if($base_url && $previous_url)
        {
            if (strpos($previous_url, $base_url) !== false) 
            {
                $is_internal_request = TRUE;
            }
        }
 
        
        if($is_internal_request)
        {
            // Setting the intended url, as without the following code the intended url is found empty
            $route_name = \Request::route()->getName();
            // $intended_url = session('_previous.url');
   

            if(!$guard && $route_name == 'login')
            {
                if(url()->previous() != route('login') 
                    && url()->previous() != route('installer_page') 
                    && url()->previous() != route('customer_login_page')
                )
                {
                    session(['url.intended' => url()->previous()]);
                }
                else
                {
                    session()->forget(['url.intended']);
                }            
                //echo session('_previous.url');                
            }

            elseif($guard == 'customer' && $route_name == 'customer_login_page')
            {
               if(url()->previous() != route('login'))
                {
                    session(['url.intended' => url()->previous()]);
                }
                else
                {
                    session()->forget(['url.intended']);
                }       
            }        
            // End of Setting the intended url

        }

        
        
     
        switch ($guard)
        {
            case 'customer':
                if (Auth::guard($guard)->check()) {
                    return redirect()->route('customer_dashboard');
                }
                break;

            default:
                if (Auth::guard($guard)->check()) {                   
                    return redirect()->route('dashboard');
                }
                break;

        }


        return $next($request);
    }
}
