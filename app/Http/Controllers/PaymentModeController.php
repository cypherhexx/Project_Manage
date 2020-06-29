<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \stdClass;
use Illuminate\Validation\Rule;

use App\Invoice;
use App\Payment;
use App\Setting;
use App\PaymentMode;
use App\Services\Pdf;



class PaymentModeController extends Controller
{    

    function offline_modes_index()
    {
        return view('payment.modes.index');
    }


    function offline_modes_paginate()
    {
        $query_key = Input::get('search');
        $search_key        = $query_key['value'];
        
        $q                  = PaymentMode::whereNull('is_online');
        $query              = PaymentMode::whereNull('is_online')->orderBy('name', 'ASC');

        $number_of_records  = $q->get()->count();




        if($search_key)
        {
            $query->where('name', 'like', $search_key.'%');               
            
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();
//

        $rec = [];

        if (count($data) > 0)
        {
            foreach ($data as $key => $row)
            {
                $checked     = ($row->inactive) ? '' : 'checked';

                $rec[] = array(
                    a_links('<a class="edit_item" data-id="'.$row->id.'" href="#">'.$row->name.'</a>' , []),
                    $row->description,
                    ' <input '.$checked.' data-id="'.$row->id.'" class="tgl tgl-ios payment_mode_status" id="cb'.$row->id.'" type="checkbox"/><label class="tgl-btn" for="cb'.$row->id.'"></label>',
                    side_by_side_links($row->id, route('delete_payment_mode', $row->id) )

                );

            }
        }


        $output = array(
            "draw" => intval(Input::get('draw')),
            "recordsTotal" => $number_of_records,
            "recordsFiltered" => $recordsFiltered,
            "data" => $rec
        );


        return response()->json($output);


    }


    public function offline_mode_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        =>  'required|unique:payment_modes',
            

        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj                    = new PaymentMode();
        $obj->name              = $request->name;
        $obj->description       = $request->description;
        $obj->inactive          = ($request->inactive) ? $request->inactive : NULL ;
        
        $obj->save();

        return response()->json(['status' => 1]);
    }


    public function offline_mode_edit(Request $request)
    {
        $obj = PaymentMode::find(Input::get('id'));

        if($obj)
        {
            return response()->json(['status' => 1, 'data' => $obj->toArray()]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }

    }


    public function offline_mode_update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id'                =>  'required',
            'name' => [
                'required',
                Rule::unique('payment_modes')->ignore($request->id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj                    = PaymentMode::find($request->id);
        $obj->name              = $request->name;
        $obj->description       = $request->description;
        $obj->inactive          = ($request->inactive) ? $request->inactive : NULL ;
        $obj->save();

        return response()->json(['status' => 1]);

    }


    function offline_change_mode_status(Request $request)
    {
        $inactive = Input::get('inactive');
        $status = ($inactive == 1) ? TRUE : NULL ;
        $rec = PaymentMode::where('id', Input::get('id'))->update(['inactive'=> $status]);

        if(count($rec) > 0)
        {
            return response()->json(['status' => 1]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }
    }

    function offline_mode_destroy(PaymentMode $mode)
    {
        try {   

            $mode->forcedelete();
            session()->flash('message', __('form.success_delete'));
            
        } catch (\Illuminate\Database\QueryException $e) {
           // Handle Integrity constraint violation
            DB::rollback();
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
        catch (\Exception  $e) {
            
            DB::rollback();
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
                        
        }

        return redirect()->back();
            
    }

    // function get_online_payment_mode_plugins()
    // {
    //     $plugins = ['stripe' => 'App\Services\PaymentGateway\Stripe' ];

    //     $custom_plugins  = config('microelephant.payment_method_classes');

    //     if(is_array($custom_plugins) && count($custom_plugins) > 0)
    //     {
    //         $plugins = $plugins + $custom_plugins;
    //     }
         
    //     return $plugins;
    // }

    function online_modes_main(Request $request)
    {   
        error_log("between");
        error_log(json_encode($request->group));
        error_log("test error log");
        $data                                               = [];
        $data['default_gateway_unique_identifier']          = 'stripe';
        $gateway_name                                       = Input::get('group');
        $gateway_name                                       = ($gateway_name) ?? $data['default_gateway_unique_identifier'];       
        $gateway_plugins                                    = PaymentMode::get_online_payment_mode_plugins();
        error_log(json_encode($gateway_plugins));
        if(is_array($gateway_plugins) && (!empty($gateway_plugins))  && isset($gateway_plugins[$gateway_name]) )
     
        {
            foreach ($gateway_plugins as $key=>$plugin) 
            {
                if(class_exists($plugin))
                {
                    $gateway_plugin     = new $plugin;

                    $data['tabs'][]     = [
                        'display_name'          => $gateway_plugin->display_name(),
                        'unique_identifier'     => $gateway_plugin->unique_identifier_id(),
                    ];

                    if($gateway_name == $key)
                    {
                        $data['view_file']          = $gateway_plugin->view_file_for_settings_page();
                        $data['unique_identifier']  = $gateway_plugin->unique_identifier_id();
                    }              

                }    
            }            
        }
        else
        {
            abort(404);
        }
        
        $payment_gateways   = Setting::where('option_key', 'payment_gateways')->get();   

        $rec = (count($payment_gateways) > 0 ) ? json_decode($payment_gateways->first()->option_value) : [];
        error_log(json_encode($data));
        if($request->group == 'stripe')
            $data['view_file']          = "payment.modes.online.stripe";
        if($request->group == 'paypal')
            {
                $data['view_file']          = "payment.modes.online.paypal";

            }
        return view('payment.modes.online.main',  compact('data'))->with('rec', $rec);
    }



    function store_online_payment_mode(Request $request)
    {

        $gateway_plugins = PaymentMode::get_online_payment_mode_plugins();

        if(!$request->unique_identifier_id && !isset($gateway_plugins[$request->unique_identifier_id]) )
        {
            session()->flash('message', 'unique_identifier_id is missing');
            return  redirect()->back();
        }

        $plugin = $gateway_plugins[$request->unique_identifier_id];

        if(!class_exists($plugin))
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return  redirect()->back();       
        }
                    
        
        $gateway_plugin     = new $plugin;

        $rules = $gateway_plugin->validation_rules();
        $rules = ['settings' => 'required|array'] + $rules;       
           
        
        $validator = Validator::make($request->all(), $rules , $gateway_plugin->validation_messages());

        if ($validator->fails()) 
        {
            pr($validator->errors());
            return redirect()->back()->withErrors($validator)->withInput();                
        }

         // payment_gateways
        $posted_data_settings       = Input::get('settings');
        $payment_mode_id            = Input::get('payment_mode_id');

        // Save the Settings
        $response = $this->save_payment_gateway_settings(
            $gateway_plugin->unique_identifier_id(), 
            $posted_data_settings[$gateway_plugin->form_input_field_name_gateway_name()] , 
            $gateway_plugin->display_name(),
            ($posted_data_settings[$gateway_plugin->form_input_field_name_gateway_status()] == 0) ? TRUE : NULL , 
            Input::get('payment_mode_id') , 
            $posted_data_settings
        );        
      

        if($response)
        {
            session()->flash('message', __('form.success_update'));        
        }
        else
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
        }

        return  redirect()->back();


      
        
    }
    

    function save_payment_gateway_settings($gateway, $label, $description, $inactive, $payment_mode_id, $posted_data_settings)
    {
        DB::beginTransaction();
        $success = false;

        try {


            // If there is already a payment mode created for the the gateway, fetch that or create new;
            $payment_mode               = ($payment_mode_id) ?  PaymentMode::find($payment_mode_id) : new PaymentMode() ;          
            $payment_mode->name         = $label;
            $payment_mode->description  = $description;
            $payment_mode->is_online    = TRUE;
            $payment_mode->inactive     = $inactive;         
            $payment_mode->save();

            // Set the Payment Mode ID in a variable to use it later below
            $payment_mode_id            = $payment_mode->id;
            
            // If there is a option key payment_gateways, then fetch that or create new
            $settings                   = Setting::firstOrCreate(['option_key' => 'payment_gateways']);

            // If the option value has value in it decode the json data otherwise create an empty object
            $payment_gateways           = ($settings->option_value) ? json_decode($settings->option_value) : new \stdClass() ;
            
            // If the payment_gateways has "stripe" information in it, get it or otherwise create an empty object
            $payment_gateways->{$gateway}   = (isset($payment_gateways->{$gateway})) ? $payment_gateways->{$gateway} : new \stdClass() ;

            // Set the payment mode id in the stripe object;
            $payment_gateways->{$gateway}->payment_mode_id  = $payment_mode_id;

            // Set all the options gathered through form submission in the stripe object
            foreach ($posted_data_settings as $key=>$value) 
            {
                $payment_gateways->{$gateway}->{$key} = $value;
            }
            
            // json ecode the value 
            $settings->option_value = json_encode($payment_gateways);

            // And finally save it
            $saved = $settings->save();



            DB::commit();
            $process_status = true;
        } 
        catch (\Exception  $e) 
        {
            $process_status = false;
            DB::rollback();            

        }

        return  $process_status;
           
    }
}
