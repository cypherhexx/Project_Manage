<?php

namespace App\Http\Controllers\Auth;

use App\CustomerRegistration;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Customer;
use App\NumberGenerator;
use App\CustomerContact;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmEmail;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidRecaptcha;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/client';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:customer');
    }

    public function showRegistrationForm()
    {
        $rec = [];
        $data   = Customer::dropdowns();

        return view('auth.register', compact('data'))->with('rec', $rec);
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $rules = [
            'contact_first_name'    => 'required|string|max:255',
            'contact_last_name'     => 'required|string|max:255',

            'name' => 'required|string|max:255',
            'contact_email' => 'required|string|email|max:255|unique:customer_contacts,email|unique:customer_registrations',
            'contact_password' => 'required|string|min:6',
            'repeat_password' => 'required|same:contact_password',
        ];


        if(is_recaptcha_enable())
        {
            $rules['g-recaptcha-response'] = ['required', new ValidRecaptcha];
        }
        
        return Validator::make($data, $rules);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));


        return view('auth.verify');
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {    
        
        $data['verification_token'] = substr(md5(uniqid(rand(), true)), 16, 16); // 16 characters long
        $data['contact_password']   = Hash::make($data['contact_password']);
        $user = CustomerRegistration::create($data);

        // Send Email Verification Link to the user
        Mail::to($user->contact_email)->send(new ConfirmEmail($user));       
       

        return $user;

    }


    function verify_email($code)
    {
        $customer = CustomerRegistration::where('verification_token', $code)->get();

        if(count($customer) > 0)
        {
            $customer = $customer->first();

            $validator = Validator::make(['contact_email' => $customer->contact_email ], [
                'contact_email' => 'required|string|email|max:255|unique:customer_contacts,email',           

            ]);

            if ($validator->fails()) 
            {
                // The customer has already been created in the mean time
                abort(404);
            }
            else
            {
                DB::beginTransaction();
                $success = false;

                try {

                    $data = $customer->toArray();

                    $data['number']                 = NumberGenerator::gen(COMPONENT_TYPE_CUSTOMER);
                    $data['currency_id']            = config('constants.default_currency_id') ;


                    $obj  = Customer::create($data);  

                    // Customer's Primary Contact                
                    $primary_contact    = new CustomerContact();

                    $primary_contact->customer_id                               = $obj->id;
                    $primary_contact->first_name                                = $customer->contact_first_name;
                    $primary_contact->last_name                                 = $customer->contact_last_name ;
                    $primary_contact->email                                     = $customer->contact_email;
                    $primary_contact->phone                                     = $customer->contact_phone;
                    $primary_contact->position                                  = $customer->contact_position;
                    $primary_contact->is_primary_contact                        = TRUE;
                    $primary_contact->password                                  = $customer->contact_password;  
                    $primary_contact->save();

                    // Delete the record from customer_registrations table
                    $customer->delete();

                    DB::commit();
                    $success = true;


                } catch (\Exception  $e) {
                    $success = false;
                    DB::rollback();

                }

                if ($success) 
                {
                    return view('auth.verified');
                } 
                else 
                {
                    abort(404);
                }

            }

        }
        else
        {
            abort(404);
        }

    }

    function resend_verification_link_page()
    {
        return view('auth.resend_email_verfification_link');
    }

    function resend_verification_link(Request $request)
    {
        $validator = Validator::make($request->all(), [            
            'email'             => 'required|email|exists:customer_registrations,contact_email',
            

        ]);

        if ($validator->fails()) 
        {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = CustomerRegistration::where('contact_email', $request->email)->get()->first();
        $user->verification_token = substr(md5(uniqid(rand(), true)), 16, 16); // 16 characters long
        $user->save();

        // Send Email Verification Link to the user
        Mail::to($user->contact_email)->send(new ConfirmEmail($user));

        $request->session()->flash('resent', 'Task was successful!');

        return view('auth.verify');

    }
}
