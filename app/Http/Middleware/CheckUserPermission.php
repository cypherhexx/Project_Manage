<?php

namespace App\Http\Middleware;

use Closure;

class CheckUserPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission_name, $is_multiple = NULL)
    {
        
        // Multiple permission check perm:[leads_view|leads_view_own],is_multiple
        // Single Permission check perm:leads_view

        /*
           // We might need the follow commented code later 
           if(preg_match('/\[([^\]]+)]/', $permission_name ))
           {
             $permission_name = explode("|", trim(str_replace(array('[', ']'),"" , $permission_name)) );         
           }
       */
       if($is_multiple)
       {
            try{
                // Convert to array
                $permissions = explode("|", trim(str_replace(array('[', ']'),"" , $permission_name)) ); 
                $permissions = array_map('trim',$permissions);
                
                if(!check_perm($permissions))
                {
                    abort(404);
                }                 
                
            } 
            catch(\Exception $e)
            {
                abort(404);
            }
       }
       else
       {
            if(!check_perm($permission_name))
            {
                abort(404);
            }   
        }   
      

        
        return $next($request);
    }
}
