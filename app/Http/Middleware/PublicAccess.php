<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class PublicAccess extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {

        $session = $request->session()->all();

        $is_logged_in_user = FALSE;
        if(is_array($session) && count($session) > 0)
        {
           $data = array_keys($session);

            foreach ($data as $value) 
            {         
               if(strpos($value, 'login_customer') !== false)
                {
                    $is_logged_in_user = TRUE;            
               
                    break;
                }
                
            }
        }

        if($is_logged_in_user)
        {
            $this->authenticate($request, $guards);
        }       
       

        return $next($request);
    }

    protected function redirectTo($request)
    {
        return route('login');
    }
}
