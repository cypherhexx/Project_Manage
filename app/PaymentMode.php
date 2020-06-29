<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Setting;
use App\Invoice;

class PaymentMode extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];


    public function payments()
    {
    	 return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
    	return $this->hasMany(Expense::class);
    }

    static function get_online_payment_gateways_dropdown_for_payment(Invoice $invoice, $currency_code)
    {
        $payment_gateways = Setting::where('option_key', 'payment_gateways')->get();

        $data =  [];

        if(count($payment_gateways) > 0 ) 
        {
            $gateway_plugins = self::get_online_payment_mode_plugins();    

            $payment_gateways = json_decode($payment_gateways->first()->option_value);

            if(!empty($payment_gateways))
            {
                foreach ($payment_gateways as $unique_identifier_id => $gateway_object_from_db) 
                {
                    $rec = self::get_gateway_plugins($invoice, $currency_code, $unique_identifier_id, $gateway_object_from_db, $gateway_plugins);

                    if($rec)
                    {
                       $data[] = $rec;
                    }
                }
            }
        } 

       return $data;
    }

    static private function get_gateway_plugins(Invoice $invoice, $currency_code, $unique_identifier_id, $gateway_object_from_db, $gateway_plugins)
    {           
       
       if(is_array($gateway_plugins) && (!empty($gateway_plugins))  && isset($gateway_plugins[$unique_identifier_id]) )
        {
            $plugin = $gateway_plugins[$unique_identifier_id];

            if(class_exists($plugin))
            {
                $gateway_plugin     = new $plugin;

                if(isset( $gateway_object_from_db->{$gateway_plugin->form_input_field_name_gateway_status()} )
                    && isset($gateway_object_from_db->{$gateway_plugin->form_input_field_name_gateway_name()})
                    && $gateway_plugin->unique_identifier_id()
                )
                {
                    $is_active          =  $gateway_object_from_db->{$gateway_plugin->form_input_field_name_gateway_status()};

                    if($is_active)
                    {
                       return [
                        'display_name_set_by_user'          => $gateway_object_from_db->{$gateway_plugin->form_input_field_name_gateway_name()} ,
                        'unique_identifier'                 => $gateway_plugin->unique_identifier_id(),
                       
                        ];
                    }       

                }                       

            }               
        }
        return FALSE;
    }


    static function get_online_payment_mode_plugins()
    {
        $plugins = ['stripe' => 'App\Services\PaymentGateway\Stripe','paypal' => 'App\Services\PaymentGateway\PayPal' ];

        $custom_plugins  = config('microelephant.payment_method_classes');

        if(is_array($custom_plugins) && count($custom_plugins) > 0)
        {
            $plugins = $plugins + $custom_plugins;
        }
         
        return $plugins;
    }


    static function get_class_instance_gateway_plugin($unique_identifier_id)
    {
        $gateway_plugins = self::get_online_payment_mode_plugins();   

        if(is_array($gateway_plugins) && (!empty($gateway_plugins))  && isset($gateway_plugins[$unique_identifier_id]) )
        {
            $plugin = $gateway_plugins[$unique_identifier_id];

            if(class_exists($plugin))
            {
                return new $plugin;
                                        

            }               
        }
        return FALSE;

    }
}
