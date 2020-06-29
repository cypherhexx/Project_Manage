<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Project;
use App\Invoice;
use Illuminate\Support\Facades\DB;
use App\CustomerContact;
use App\Services\Pdf;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {        

        $currency_id            = (auth()->user()->customer->currency_id) ? auth()->user()->customer->currency_id : config('constants.default_currency_id');

        $data                   = Invoice::stat($currency_id , Auth::user()->customer_id);
                
        $data['project_stat']   = Project::statistics(Auth::user()->customer_id);


        return view('customer_panel.home', compact('data'));
    }


    function invoices()
    {
        return view('customer_panel.invoices');   
    }

    function estimates()
    {
        return view('customer_panel.estimates');   
    }

    public function user_profile()
    {

        $rec   = CustomerContact::find(auth()->user()->id);
        return view('customer_panel.profile')->with('rec', $rec);
    }

    public function update_profile(Request $request)
    {
        $user_id    = auth()->user()->id; 

        $validator  = Validator::make($request->all(), [
            'first_name'        =>  'required',
            'last_name'         =>  'required',
            'email' => [
                'required',
                'email',
                Rule::unique('customer_contacts')->ignore($user_id),
            ],

        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $obj                            = CustomerContact::find($user_id);
        $obj->first_name                = $request->first_name;
        $obj->last_name                 = $request->last_name ;
        $obj->email                     = $request->email;
        $obj->phone                     = $request->phone;
        $obj->position                  = $request->position;
        $obj->save();
               

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();


    }

    function change_password()
    {
        return view('customer_panel.change_password');
    }


    public function update_password(Request $request)
    {
        $user_id    = auth()->user()->id; 

        $validator  = Validator::make($request->all(), [            
            'current_password' => ['required', function ($attribute, $value, $fail) {
                if (!\Hash::check($value, auth()->user()->password)) {
                    return $fail(__('The current password is incorrect.'));
                }
            }],

            'new_password'                  =>  'required',
            'confirm_password'              =>  'required|same:new_password',
            

        ]);

        if ($validator->fails()) 
        {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $obj                            = CustomerContact::find($user_id);
        $obj->password                  = Hash::make($request->new_password) ;
        $saveed = $obj->save();
               
        if($saveed)
        {
            session()->flash('message', __('form.success_update'));
        }
        else
        {
            
        }
        
        return  redirect()->back();


    }


    function customer_statement_page(Request $request)
    {   
        $first_day_of_the_month  = (new \DateTime('first day of '. date('F') ))->format("Y-m-d");
        $last_day_of_the_month   = (new \DateTime('last day of '. date('F') ))->format("Y-m-d");
       

        $customer_id        =  auth()->user()->customer_id;
        $date_from          = ($request->from) ? $request->from : $first_day_of_the_month;
        $date_to            = ($request->to) ? $request->to : $last_day_of_the_month;
    
        $customer           = new \App\Customer();
        $records            = $customer->get_records_for_statement($customer_id, $date_from, $date_to);

        $invoiced_amount    = 0;
        $payment_amount     = 0;

        if(!empty($records))
        {
            foreach ($records as $row) 
            {
                if($row->type == 'invoice')
                {
                    $invoiced_amount += $row->amount;
                }
                if($row->type == 'credit_note')
                {
                    $invoiced_amount += -$row->amount;
                }
                if($row->type == 'payment')
                {
                    $payment_amount += $row->amount;
                }
            }
        }
        
        $data['beginning_balance']      = $customer->get_beginning_balance_for_statement($customer_id, $date_from);
        
        $data['invoiced_amount']        = $invoiced_amount;
        $data['payment_amount']         = $payment_amount;
        $data['balance_due']            = ($data['beginning_balance'] + $invoiced_amount) - $payment_amount;

        
        $data['date_from']              = date("M d, Y", strtotime($date_from));
        $data['date_to']                = date("M d, Y", strtotime($date_to));
      

        $data['currency_symbol']        = (isset(auth()->user()->customer->currency->symbol)) ? auth()->user()->customer->currency->symbol : config('constants.default_currency_symbol');
        

        if($request->pdf)
        {
            $pdf    = new Pdf();

            $data['html']   = view('customer_panel.partials.statement_main', compact('data'))->with('rec', $records)->render();

            $data['page_title'] = 'Account Statement';

            $html   = view('layouts.print.template', compact('data'))->with('rec', $records)->render();

            $file_name = str_replace(" ", "_",  'account_statement_'.$data['date_from']. '-'. $data['date_to'] );            
            
            $pdf->download($html, $file_name);
        }
        
        $data['url_for_pdf_download']   = route('cp_customer_statement_page'). '?from=' . date("Y-m-d", strtotime($data['date_from'])).'&to='. date("Y-m-d", strtotime($data['date_to'])). '&pdf=1';

        return view('customer_panel.statement', compact('data'))->with('rec', $records);
    }




}