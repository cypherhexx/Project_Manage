<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;


class SetUserPermission
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
        $permissions = [];

        // If the User is assigned with a role but not an administrator
        if(Auth::user()->role_id)
        {
            $data = DB::select("SELECT name FROM role_permissions WHERE role_id = ?", [ Auth::user()->role_id ]);
         
            if(count($data) > 0)
            {
                // Coverting to Array
                $permissions = array_column( json_decode(json_encode($data), True) , 'name');           
             }
         
        }
     
        // Set User Permissions
        Config::set('constants.user_permissions', $permissions);
      

        return $next($request);
    }
}
