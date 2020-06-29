<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;

class Setting extends Model {

    protected $fillable = ['option_key', 'option_value'];
    public $timestamps = false;

    static function get_prefix_by_component_id($component_id)
    {
         $prefix_list = [
             COMPONENT_TYPE_PROPOSAL    => 'PRO',
             COMPONENT_TYPE_ESTIMATE    => 'EST',
             COMPONENT_TYPE_INVOICE     => 'INV',
             COMPONENT_TYPE_PAYMENT     => 'PMT',
             COMPONENT_TYPE_VENDOR      => 'VEN',
             COMPONENT_TYPE_CUSTOMER    => 'CUS',
             COMPONENT_TYPE_TICKET      => 'TIC',
             COMPONENT_TYPE_TASK        => 'TSK',
             COMPONENT_TYPE_PROJECT     => 'PRJ',
             COMPONENT_TYPE_CREDIT_NOTE => 'CRN',
         ];

         return (isset($prefix_list[$component_id])) ? $prefix_list[$component_id] : '';
    }


    private static function get_list_of_time_zone()
    {

        $timezone_identifiers = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

        foreach ($timezone_identifiers as $r)
        {
            $data[$r]     = $r;
        }
        return $data;

    }
    // We have this same function in company website system as well.
    private static function get_date_format_dropdowns()
    {
        $separator = " | ";

        $data = array();
        $data[0]['display_format'] 		= "M/d/yyyy";
        $data[0]['id'] 	                = "n/j/Y";
        $data[0]['name'] 	            =  date($data[0]['id'])   ;

        $data[1]['display_format'] 		= "‎M/d/yy";
        $data[1]['id'] 	                = "n/j/y";
        $data[1]['name'] 	            =  date($data[1]['id'])   ;

        $data[2]['display_format'] 		= "MM/dd/yy";
        $data[2]['id'] 	                = "m/d/y";
        $data[2]['name'] 	            =  date($data[2]['id'])   ;

        $data[3]['display_format'] 		= "MM/dd/yyyy";
        $data[3]['id'] 	                = "m/d/Y";
        $data[3]['name'] 	            =  date($data[3]['id'])   ;

        $data[4]['display_format'] 		= "yy/MM/dd";
        $data[4]['id'] 	                = "y/m/d";
        $data[4]['name'] 	            =  date($data[4]['id'])   ;


        $data[5]['display_format'] 		= "yyyy-MM-dd";
        $data[5]['id'] 	                = "Y-m-d";
        $data[5]['name'] 	            =  date($data[5]['id'])   ;


        $data[6]['display_format'] 		= "dd-MMM-yy";
        $data[6]['id'] 	                = "d-M-y";
        $data[6]['name'] 	            =  date($data[6]['id'])   ;

        $data[7]['display_format'] 		= "dd/MM/yyyy";
        $data[7]['id'] 	                = "d/m/Y";
        $data[7]['name'] 	            =  date($data[7]['id'])   ;

        foreach($data as $r)
        {
            $k[$r['id']] = $r['name'];
        }
        return $k;
    }


    public static function settings_dropdown()
    {

        //$data['list_of_date_formats']       = self::get_date_format_dropdowns();

        $data['list_of_digits_after_decimal'] = array(
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4
        );

        $data['decimal_symbol'] = array(
            '.' => '.',
            ',' => ',',
            
        );

        $data['list_of_digit_grouping_methods'] = array(
            config('constants.FORMAT_CURRENCY_METHOD_ONE')      => "4,29,49,67,295",
            config('constants.FORMAT_CURRENCY_METHOD_TWO')      => "4,29,49,67,295",
            config('constants.FORMAT_CURRENCY_METHOD_THREE')    => "42,9496,7295"
        );

        $data['time_zone']  = self::get_list_of_time_zone();

        $data['yes_and_no']  = array(1 => __('form.yes'), '' => __('form.no') );


        $data['languages']  = get_languges();

        return $data;
    }

    public static function setup_app_config()
    {  
       
        $settings = self::whereNull('auto_load_disabled')->get();       
        

        if(count($settings) > 0)
        {
            foreach ($settings as $row)
            {
                $rec[$row['option_key']] = $row['option_value'];

                Config::set('constants.'.$row['option_key'], $row['option_value']);
            }

            $rec = (object) $rec;                  

            $rec->number_of_digits_after_decimal = 2;

            // If job queue is disabled from settings
            if(isset($rec->disable_job_queue) && $rec->disable_job_queue)
            {
                config(['queue.default'=>'sync']);
            }

            // Currency
            $currency = Currency::default()->get()->first();

            if(isset($currency) && $currency)
            {
                Config::set('constants.default_currency_id', $currency->id);
                Config::set('constants.default_currency_code', $currency->code);
                Config::set('constants.default_currency_symbol', $currency->symbol);
            }
             // End of currency

           
            Config::set('constants.company_name', $rec->company_name);
            Config::set('app.name', $rec->company_name);        

            // If it's not customer then the user is a general user/team member. Check if the user is involved in any project
            // We need to set the following flag for permission purposes.
            if(auth()->user() && (!isset(auth()->user()->customer_id)))
            {
                $project_ids = \App\Project::get_project_ids_that_the_current_user_is_involved_in();           
                Config::set('constants.is_involved_in_project', (count($project_ids) > 0) ? TRUE : FALSE );

            }

            Config::set('constants.company_full_address', $rec->company_address  ."<br>". $rec->company_city . " ". $rec->company_state 
                . "<br>" . $rec->company_zip_code ."<br>". $rec->company_country);

            Config::set('constants.datatable_results_per_page', 15);

            Config::set('constants.digit_grouping_method', $rec->digit_grouping_method);
            Config::set('constants.db_date_format', dateformat_PHP_to_MYSQL($rec->date_format));
            Config::set('constants.date_format', $rec->date_format);
            Config::set('constants.javascript_date_format', dateformat_PHP_to_Javascript($rec->date_format) );
            
            if(isset($currency) && $currency)
            {
                Config::set('constants.money_format', (object) array(
                    "currency" => (object) array(
                        'symbol'    => $currency->symbol ." ",
                        'format'    => "%s%v",
                        'decimal'   => $rec->decimal_symbol,
                        'thousand'  => $rec->digit_grouping_symbol,
                        'precision' => $rec->number_of_digits_after_decimal ,
                        'round_to'  => $rec->round_to ,
                    ),
                    "number" => (object) array(
                        'decimal'   => $rec->decimal_symbol,
                        'thousand'  => $rec->digit_grouping_symbol,
                        'precision' => $rec->number_of_digits_after_decimal ,
                    )
                ));
            }
            Config::set('constants.is_price_round_enabled', $rec->is_price_round_enabled);
            Config::set('app.timezone', $rec->time_zone );

            date_default_timezone_set($rec->time_zone);

            

            // Set Email Settings
            if(isset($rec->disable_email) && $rec->disable_email)
            {
                 // Email has been disabled from settings, so changing the driver will send fake emails
                 config()->set('mail', array_merge(config('mail'), [
                        'driver' => 'log'
                ]));
            }
            else
            {               
                if(isset($rec->company_email_send_using) && $rec->company_email_send_using)
                {
                    config()->set('mail', array_merge(config('mail'), [
                        'driver'            => $rec->company_email_send_using,
                        'host'              => $rec->company_email_smtp_host,
                        'port'              => $rec->company_email_smtp_port,
                        'from'              => [
                            'address'               => $rec->company_email_from_address,
                            'name'                  => $rec->company_name,
                        ],
                        'encryption'    => $rec->company_email_encryption,
                        'username'      => $rec->company_email_smtp_username,
                        'password'      => $rec->company_email_smtp_password,
                    ]));

                    if($rec->company_email_send_using == 'mailgun')
                    {
                        config()->set('services', array_merge(config('services'), [
                            'mailgun'       => [
                                'domain'            => $rec->company_email_mailgun_domain,
                                'secret'            => $rec->company_email_mailgun_key,
                            ]
                        ]));
                    }
                }                


            }
           
           


            // Pusher
            Config::set('constants.is_pusher_enable', FALSE);
            $pusher = config('constants.pusher');
            if($pusher)
            {
                $pusher = json_decode($pusher);

                if(isset($pusher->is_enable) && $pusher->is_enable)
                {
                    Config::set('constants.is_pusher_enable', TRUE);
                }
            }

            // Set Language
            $lang = ($rec->default_language) ? $rec->default_language : 'en';
            App::setLocale($lang);
         
        }

        


    }

    
    private static function available_short_code_for_ticket()
    {
         return [
            '{customer_name}', '{department_name}', '{ticket_number}', '{ticket_subject}', 
            '{ticket_priority}', '{ticket_link}', '{ticket_message}' 
        ];    
    }

    public static function list_of_email_templates()
    {
        return [

            // Ticket
            COMPONENT_TYPE_TICKET => [ 
                'component_name' => __('form.ticket'),
                'templates' => [
                    [
                        'name'  => 'new_ticket_opened_sent_to_customer',    
                        'title' => __('form.new_ticket_opened_sent_to_customer'),
                        'route' => route('settings_email_template_page', 'new_ticket_opened_sent_to_customer'),
                        'short_codes' => self::available_short_code_for_ticket()
                    ],
                    [
                        'name'  => 'new_ticket_opened_sent_to_non_customer',    
                        'title' => __('form.new_ticket_opened_sent_to_non_customer'),
                        'route' => route('settings_email_template_page', 'new_ticket_opened_sent_to_non_customer'),
                        'short_codes' => self::available_short_code_for_ticket()
                    ],
                   
                    [
                        'name'  => 'ticket_reply_from_team_sent_to_customer',    
                        'title' => __('form.ticket_reply_from_team_sent_to_customer'),
                        'route' => route('settings_email_template_page', 'ticket_reply_from_team_sent_to_customer'),
                        'short_codes' => self::available_short_code_for_ticket()
                    ],
                     [
                        'name'  => 'ticket_reply_from_team_sent_to_non_customer',    
                        'title' => __('form.ticket_reply_from_team_sent_to_non_customer'),
                        'route' => route('settings_email_template_page', 'ticket_reply_from_team_sent_to_non_customer'),
                        'short_codes' => self::available_short_code_for_ticket()
                    ],
                    

                ],

             ],
            // End of Component

            // ESTIMATE
            COMPONENT_TYPE_ESTIMATE => [ 
                'component_name' => __('form.estimate'),
                'templates' => [
                    [
                        'name'  => 'email_template_estimate_sent_to_customer',    
                        'title' => __('form.send_estimate_to_customer'),
                        'route' => route('settings_email_template_page', 'email_template_estimate_sent_to_customer'),
                        'short_codes' => ['{contact_first_name}', '{contact_last_name}', '{estimate_number}', '{email_signature}']
                                                     
                    ],
                    
                    

                ],

             ], 
             // End of Component

            // Invoice
            COMPONENT_TYPE_INVOICE => [ 
                'component_name' => __('form.invoice'),
                'templates' => [
                    [
                        'name'  => 'email_template_invoice_sent_to_customer',    
                        'title' => __('form.email_template_invoice_sent_to_customer'),
                        'route' => route('settings_email_template_page', 'email_template_invoice_sent_to_customer'),
                        'short_codes' => ['{contact_first_name}', '{contact_last_name}', '{invoice_number}', '{email_signature}']
                                                     
                    ],
                    
                    

                ],

             ], 
             // End of Component

             // Proposal
            COMPONENT_TYPE_PROPOSAL => [ 
                'component_name' => __('form.proposal'),
                'templates' => [
                    [
                        'name'  => 'email_template_proposal_sent_to_customer',    
                        'title' => __('form.email_template_proposal_sent_to_customer'),
                        'route' => route('settings_email_template_page', 'email_template_proposal_sent_to_customer'),
                        'short_codes' => [
                                            '{contact_name}', 
                                            '{proposal_number}', 
                                            '{proposal_title}',
                                            '{proposal_total}',
                                            '{proposal_open_till}', 
                                            '{email_signature}',
                                            '{proposal_link}', 
                                                                    
                                        ]
                                                     
                    ],
                    
                    

                ],

             ], 
             // End of Component

        ];
    }


    static function get_email_template_by_name($template_name)
    {
        $records = self::where('option_key', $template_name)->get();

        if(count($records) > 0)
        {
            return json_decode($records->first()->option_value);
        }

        return FALSE;       
    }

    static function get_email_template_details_by_name($template_name)
    {
        $templates = self::list_of_email_templates();

        if(count($templates) > 0)
        {
            foreach ($templates as $row) 
            {
                if(isset($row['templates']) && (count($row['templates']) > 0) )
                {
                    foreach ($row['templates'] as $template) 
                    {
                        if($template['name'] == $template_name)
                        {
                            return $template;
                        }
                    }
                }
            }
        }
        
        return FALSE;
    }

    static function isJSON($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) ? true : false;
    }


    static function get_setting($key)
    {
        $records = self::where('option_key', $key)->get();
        
        if(count($records) > 0)
        {   
            $string = $records->first()->option_value;

            if(is_string($string) && is_array(json_decode($string, true)))
            {
                return json_decode($string);    
            }
            else
            {
                return $string;
            }
            
        }

        return FALSE; 
    }

    
}
