<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Rules\ValidRecaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }


 

        
    // Overriding method of Illuminate\Foundation\Auth\AuthenticatesUsers;
    public function showLoginForm()
    {

        // Logging out the other type of user - Customer (just in case)
        // Auth::logout();

        return view('auth.login');
    }

    // Overriding method of Illuminate\Foundation\Auth\AuthenticatesUsers;
    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username()   => 'required|string',
            'password'          => 'required|string',
        ];

        if(is_recaptcha_enable())
        {
            $rules['g-recaptcha-response'] = ['required', new ValidRecaptcha];
        }

        $this->validate($request, $rules);
    }


}
