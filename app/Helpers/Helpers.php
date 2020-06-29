<?php

// For Below PHP  7.3
if (!function_exists('is_countable')) 
{
    function is_countable($var) {
        return (is_array($var) || $var instanceof Countable);
    }
}

include_once 'form_helper.php';
include_once 'currency_helper.php';

function pr($data)
{
    echo "<pre>";
    print_r($data);
    die();
}
function debug($e)
{
    echo $e->getMessage() . " <br> " . $e->getLine(). "<br>" . $e->getFile();
    die();
}
function vue_click_link($main_text, $id, $route='#')
{
    return ' <a class="showInformation" data-id="'.$id.'" href="'.$route.'">'.$main_text.'</a>';
}

function anchor_link($main_text, $link, $newTab = NULL, $permission = NULL)
{
    $newTab = (isset($newTab) && $newTab == TRUE) ? 'target="_blank"' : '';
    return ' <a '.$newTab.' class="" href="'.$link.'">'.$main_text.'</a>';
}
function a_links($main_text, array $option_links)
{
    $data = $main_text."<div style='min-height: 25px;'><div class='row-options'>";

    foreach ($option_links as $link)
    {
        if(isset($link['permission']) && check_perm($link['permission']))
        {
            $newTab = (isset($link['new_tab']) && $link['new_tab'] == TRUE) ? 'target="_blank"' : '';
            $data .= ' <a '.$newTab.' class="'.$link['action_class'].'" href="'.$link['action_link'].'">'.$link['action_text'].'</a>';    
        }
        
    }
    $data .= '</div></div>';

    return $data ;
}

function side_by_side_links($id, $delete_link)
{
    $option = "";
    
    if($id)
    {
        $option .= '<a class="edit_item btn btn-light btn-sm" data-id="'.$id.'" href="#"><i class="far fa-edit"></i></a> ';
    }
    if($delete_link)
    {
        $option .= '<a class="delete_item btn btn-danger btn-sm" href="' . $delete_link . '"><i class="far fa-trash-alt"></i></a>';
    }
    return $option;
}

function delete_link($delete_link)
{
    return '<a class="delete_item btn btn-danger btn-sm" href="' . $delete_link . '"><i class="far fa-trash-alt"></i></a>';
        
}

function action_links($edit_url, $delete_url, $additional_links = NULL)
{
    return '<div class="btn-group">
      <a class="btn btn-secondary btn-sm dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        ' . __("form.action") . '
      </a>
      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink">
        <a class="dropdown-item" href="' . $edit_url . '">' . __("form.edit") . '</a>
        <a class="dropdown-item delete_item" href="' . $delete_url . '">' . __("form.delete") . '</a>
        '.$additional_links.'
      </div>
    </div>';

}




    function like_search_wildcard_gen($search_key)
    {
        return '%' .$search_key.'%';
    }

 

    function remove_commas($price)
    {

        if(!empty($price) || $price != "")
        {
            // Removing Comma
            $string =  str_replace(",", "", $price);
            $price = preg_replace('/\s+/', '', $string);
            // Rounding Number Based on precision set by the user

            $money_format_options = config('constants.money_format');
            return number_format((float)$price, $money_format_options->number->precision , '.', '');
        }
        else
        {
            return FALSE;
        }

    }

    function get_date_from_range($date_range)
    {
        $data['date_from']              = NULL;
        $data['date_to']                = NULL;

        if($date_range)
        {
            list($date_from, $date_to)  = explode("-", $date_range);
            $date_from                  = str_replace('/', '-', trim($date_from) );
            $date_to                    = str_replace('/', '-', trim($date_to));
            $data['date_from']          = date2sql(trim($date_from));
            $data['date_to']            = date2sql(trim($date_to));
        }
        return $data;
    }

    function get_month_list_from_date_range($start_date, $end_date)
    {
        $begin = new DateTime($start_date);
        $end = new DateTime($end_date);

        while ($begin <= $end)
        {
            $months[] = $begin->format('m');
            $begin->modify('first day of next month');
        }
        return $months;
    }

    function sql2date_need_attention($date)
    {
        return date(config('constants.date_format'), strtotime($date));
    }

    function sql2date($date)
    {
        if($date)
        {
            return date("d-m-Y", strtotime($date));    
        }
        
    }

    function date2sql_needs_attention($date)
    {
        $output = "";

        try {
            $output = DateTime::createFromFormat(config('constants.date_format'), trim($date));
        }
        catch(Exception $e)
        {

        }
        if($output)
        {
            return $output->format('Y-m-d');
        }
        else
        {
            return date("Y-m-d", strtotime($date));
        }


    }

    function date2sql($date)
    {
       if($date)
       {
         return date("Y-m-d", strtotime($date));
       }
       else
       {
        return NULL;
       }

    }

    function time_to_decimal($time)
    {
        if($time)
        {
            $timeArr = explode(":", $time);
            // return $timeArr[0] + round(($timeArr[1] / 60), 2);
            $part_three = (isset($timeArr[2]) ? ($timeArr[2]/3600) : 0);
            return round($timeArr[0] + ($timeArr[1]/60) +  $part_three , 2);

            //return round($timeArr[0] + ($timeArr[1] / 60), 2);
        }
        
    }


    function bottom_toolbar($text = NULL)
    {
        $text = ($text) ? $text :__('form.submit');
        
        ob_start();
        ?>
        <div class="row bottom-toolbar">
            <div  class="col-md-12">
                <div style="text-align: right;">
                    <input type="submit" class="btn btn-primary" value="<?php echo $text; ?>"/>

                </div>
            </div>
        </div>
        <?php

        flush();
        ob_flush();
        ob_end_flush();
    }

    function showErrorClass($errors, $key)
    {
        if($errors->has($key)) { echo 'is-invalid'; }
    }
    
    function showError($errors, $key)
    {
         if($errors->has($key)) { echo $errors->first($key) ; }
    }


    function dateformat_PHP_to_Javascript($php_format)
    {
        $SYMBOLS_MATCHING = array(
            // Day
            'd' => 'dd',
            'D' => 'D',
            'j' => 'd',
            'l' => 'DD',
            'N' => '',
            'S' => '',
            'w' => '',
            'z' => 'o',
            // Week
            'W' => '',
            // Month
            'F' => 'MM',
            'm' => 'mm',
            'M' => 'M',
            'n' => 'm',
            't' => '',
            // Year
            'L' => '',
            'o' => '',
            'Y' => 'yyyy',
            'y' => 'yy',
            // Time
            'a' => '',
            'A' => '',
            'B' => '',
            'g' => '',
            'G' => '',
            'h' => '',
            'H' => '',
            'i' => '',
            's' => '',
            'u' => ''
        );
        $jqueryui_format = "";
        $escaping = false;
        for($i = 0; $i < strlen($php_format); $i++)
        {
            $char = $php_format[$i];
            if($char === '\\') // PHP date format escaping character
            {
                $i++;
                if($escaping) $jqueryui_format .= $php_format[$i];
                else $jqueryui_format .= '\'' . $php_format[$i];
                $escaping = true;
            }
            else
            {
                if($escaping) { $jqueryui_format .= "'"; $escaping = false; }
                if(isset($SYMBOLS_MATCHING[$char]))
                    $jqueryui_format .= $SYMBOLS_MATCHING[$char];
                else
                    $jqueryui_format .= $char;
            }
        }
        return $jqueryui_format;
    }

    function dateformat_PHP_to_MYSQL($dateFormat)
    {
        $formula = array(
            'D' => 'd',
            'j' => 'e',
            'm' => 'm',
            'M' => 'M',
            'n' => 'c',
            'Y' => 'Y',
            'y' => 'y'
        );

        $separator = NULL;
        $dateFormatArray = explode("-", $dateFormat);
        if(is_countable($dateFormatArray) && count($dateFormatArray) > 2)
        {
            $separator = "-";
        }
        else
        {
            $dateFormatArray = explode("/", $dateFormat);
            if(is_countable($dateFormatArray) && count($dateFormatArray) > 2)
            {
                $separator = "/";
            }
        }
        if($separator != NULL)
        {
            foreach($dateFormatArray as $key => $value)
            {
                $dateFormatArray[$key] = (array_key_exists($value , $formula)) ? $formula[$value] : $value;
            }
            $mysqlDateFormat = '%'.$dateFormatArray[0]. $separator . '%'.$dateFormatArray[1]. $separator . '%'.$dateFormatArray[2] ;
        }

        return $mysqlDateFormat;

    }

    function is_in_object($array_object, $key, $value)
    {
       
        if(!empty($array_object) && is_array($array_object))
        {
            foreach ($array_object as $row) 
            {
                if($row->{$key} == $value)
                {
                    return $row;
                }
            }
            return FALSE;
        }
        
    }

    function display_tax_rate_in_item_list($item_taxes, $rec)
    {

       $count = 1;
       $number_of_tax_in_this_item = (is_countable($item_taxes)) ? count($item_taxes) : 0;       

        if(isset($rec->array_of_taxes_used) && !empty($rec->array_of_taxes_used) && !empty($item_taxes))
        {
            if(is_array($rec->array_of_taxes_used) && is_array($item_taxes))
            {
                foreach($item_taxes as $item_tax)
                {
                    if($t = is_in_object($rec->array_of_taxes_used, 'id', $item_tax))
                    {
                        echo str_replace($t->rate.'%',"", $t->name) . " ". number_format($t->rate, 2) . '%';
                        if($count != $number_of_tax_in_this_item)
                        {
                         $count++;
                         echo "<br>";
                        }
                    }              
                    
                }
            }
        }
    }




function convert_to_quantity_format($number)
{

    return number_format((float)$number, 2, '.', ',');
}

function is_active_nav($item_name, $group_name)
{
    return ($group_name == $item_name) ? 'active': '';
}


// 
function exerpt($string,$length=100,$append="&hellip;") {
  $string = trim($string);

  if(strlen($string) > $length) {
    $string = wordwrap($string, $length);
    $string = explode("\n", $string, 2);
    $string = $string[0] . $append;
  }

  return $string;
}


define("TEMPORARY_FOLDER_IN_STORAGE", 'public/tmp/');

// Regex Pattern
define("REGEX_PATTERN_TEAM_MEMBER_FROM_STRING", '/@\[[^\]]*\]/');
define("REGEX_PATTERN_TEAM_MEMBER_FILTER_ARRAY", ['@[' => '', ']' =>''] );
define("REGEX_PATTERN_ATTACHMENT_FROM_STRING", '/@attach\[[^\]]*\]/');
define("REGEX_PATTERN_ATTACHMENT_FILTER_ARRAY", ['@attach[' => '', ']' =>''] );

define("REGEX_PATTERN_GENERIC_FROM_STRING", '/@\[[^\]]*\]/');
define("REGEX_PATTERN_GENERIC_FILTER_ARRAY", ['@[' => '', ']' =>''] );

define("REGEX_PATTERN_EXTRACT_TICKET_FROM_EMAIL_SUBJECT", '/(\[TIC.*?\])/'); // [TIC-8799234324]
define("REGEX_PATTERN_EXTRACT_REPLY_MESSEGE_FROM_EMAIL_BODY", '/##-.*?\-##/'); // ##- Please type your reply above this line -##





// User Types
define("USER_TYPE_TEAM_MEMBER", 1);
define("USER_TYPE_CUSTOMER", 2);
define("USER_TYPE_VENDOR", 3);
define("USER_TYPE_POTENTIAL_CUSTOMER", 4);

define("DEFAULT_USER_PASSWORD", 123456);



define("COMPONENT_TYPE_PROJECT", 1);
define("COMPONENT_TYPE_INVOICE", 2);
define("COMPONENT_TYPE_CUSTOMER", 3);
define("COMPONENT_TYPE_ESTIMATE", 4);
define("COMPONENT_TYPE_CONTRACT", 5);
define("COMPONENT_TYPE_TICKET", 6);
define("COMPONENT_TYPE_EXPENSE", 7);
define("COMPONENT_TYPE_LEAD", 8);
define("COMPONENT_TYPE_PROPOSAL", 9);
define("COMPONENT_TYPE_TASK", 10);
define("COMPONENT_TYPE_PAYMENT", 11);
define("COMPONENT_TYPE_VENDOR", 12);
define("COMPONENT_TYPE_TEAM_MEMBER", 13);
define("COMPONENT_TYPE_TIMESHEET", 14);
define("COMPONENT_TYPE_CREDIT_NOTE", 15);
// Task Statuses

define("TASK_STATUS_BACKLOG", 1);
define("TASK_STATUS_IN_PROGRESS", 2);
define("TASK_STATUS_TESTING", 3);
define("TASK_STATUS_AWAITING_FEEDBACK", 4);
define("TASK_STATUS_COMPLETE", 5);


// Discount Types
define("DISCOUNT_TYPE_BEFORE_TAX", 1);
define("DISCOUNT_TYPE_AFTER_TAX", 2);

// Discount Method
define("DISCOUNT_METHOD_PERCENTAGE", 1);
define("DISCOUNT_METHOD_FIXED", 2);

// Lead Statuses
define("LEAD_STATUS_CUSTOMER", 1);

// Invoice Statuses
define("INVOICE_STATUS_PAID", 1);
define("INVOICE_STATUS_UNPAID", 2);
define("INVOICE_STATUS_PARTIALLY_PAID", 3);
define("INVOICE_STATUS_OVER_DUE", 4);
define("INVOICE_STATUS_CANCELED", 5);
define("INVOICE_STATUS_DRAFT", 6);


// Proposal Statuses
define("PROPOSAL_STATUS_DRAFT", 1);
define("PROPOSAL_STATUS_SENT", 2);
define("PROPOSAL_STATUS_OPEN", 3);
define("PROPOSAL_STATUS_REVISED", 4);
define("PROPOSAL_STATUS_DECLINED", 5);
define("PROPOSAL_STATUS_ACCEPTED", 6);
define("PROPOSAL_STATUS_EXPIRED", 7);

// Estimate Statuses
define("ESTIMATE_STATUS_DRAFT", 1);
define("ESTIMATE_STATUS_SENT", 2);
define("ESTIMATE_STATUS_EXPIRED", 3);
define("ESTIMATE_STATUS_DECLINED", 4);
define("ESTIMATE_STATUS_ACCEPTED", 5);


// Proposal Statuses
define("PROJECT_STATUS_NOT_STARTED", 1);
define("PROJECT_STATUS_IN_PROGRESS", 2);
define("PROJECT_STATUS_ON_HOLD", 3);
define("PROJECT_STATUS_CANCELLED", 4);
define("PROJECT_STATUS_FINISHED", 5);



// Credit Note Statuses
define("CREDIT_NOTE_STATUS_OPEN", 1);
define("CREDIT_NOTE_STATUS_ADJUSTED", 2);
define("CREDIT_NOTE_STATUS_VOID", 3);



// Billing Type
define("BILLING_TYPE_FIXED_RATE", 1);
define("BILLING_TYPE_PROJECT_HOURS", 2);
define("BILLING_TYPE_TASK_HOURS", 3);


// Billing Type
define("SEPARATOR_TAX_NAME_RATE", ' '); // <--- Kept empty space intentionally


define("AVATAR_SMALL_THUMBNAIL_SIZE", '_32x32');


define("LOG_NAME_PROJECT", 'project_');
// define("LOG_NAME_LEAD", 'lead_');
define("LOG_NAME_DEFAULT", 'default');

function activity_log_name_by_componet_id($component_id)
{
    $data = [
        COMPONENT_TYPE_CUSTOMER     => 'customer_',
        COMPONENT_TYPE_LEAD         => 'lead_',
        COMPONENT_TYPE_PROJECT      => 'project_',
        COMPONENT_TYPE_PROPOSAL     => 'proposal_',
        COMPONENT_TYPE_TICKET       => 'ticket_',
        COMPONENT_TYPE_VENDOR       => 'vendor_',
        
        
    ];

    return isset($data[$component_id]) ? $data[$component_id] : NULL ;
}


// Ticket Statuses
define("TICKET_STATUS_OPEN", 1);
define("TICKET_STATUS_IN_PROGRESS", 2);
define("TICKET_STATUS_ANSWERED", 3);
define("TICKET_STATUS_ON_HOLD", 4);
define("TICKET_STATUS_CLOSED", 5);


function get_avatar_small_thumbnail($photo)
{
    if($photo)
    {
        if(\Storage::exists($photo))
        {
            $path = asset(\Storage::url($photo));
            $file_name = pathinfo($path, PATHINFO_FILENAME);

            if($file_name)
            {
                return str_replace($file_name, $file_name.AVATAR_SMALL_THUMBNAIL_SIZE, $path);
            } 
        }      
        
    }
    return asset('images/user-placeholder.jpg');
    
}

// ShortCode Prefix
function create_short_code_for_attachment()
{
    return '@attach[' . uniqid(). ']';
}


function in_assoc_array($array, $key, $value)
{
    if(is_array($array) && count($array) > 0)
    {

        foreach ($array as $k=>$v)
        {
            if(isset($v[$key]) && $v[$key] == $value)
            {
                return TRUE;
            }
        }
    }
    return FALSE;
}


function parse_tax_string($display_as)
{
    $tax_string_array = explode("_", $display_as);
    $data['rate'] = $tax_string_array[0];
    $data['name'] = ucfirst(str_replace("_", " ", str_replace($data['rate'] ."_", "", $display_as)));

    return $data;
}

function get_url_route_name_by_component_id($component_id)
{
    $data = [
        COMPONENT_TYPE_LEAD         => 'show_lead_page',
        COMPONENT_TYPE_CUSTOMER     => 'edit_customer_page',
        COMPONENT_TYPE_PROJECT      => 'show_project_page',
        COMPONENT_TYPE_TICKET       => 'show_ticket_page',
    ];

    return $data[$component_id];
}

function get_url_route_name_by_model_class($class_name)
{
    $data = [
        \App\Lead::class            => 'show_lead_page',
        \App\Customer::class        => 'edit_customer_page',
        \App\Project::class         => 'edit_project_page',
        \App\Ticket::class          => 'show_ticket_page',
    ];

    return $data[$class_name];
}



function short_code_parser_email($content)
{
    
    $replacements = array(
        'company_name' => config()->get('constants.company_name'),
        'company_logo' => '<img src="'.get_company_logo().'"/>' 
        
    );

    $content = preg_replace_callback(
        REGEX_PATTERN_GENERIC_FROM_STRING ,
        function (array $m) use ($replacements) {
            $item = strtr(trim($m[0]), REGEX_PATTERN_GENERIC_FILTER_ARRAY );
            return array_key_exists($item, $replacements) ? $replacements[$item] : '';
        },
        $content
    );

    return $content;
}

function short_code_parser($content, $replacements)
{    

    $content = preg_replace_callback(
                '/{[^}]*\}/',
                function (array $m) use ($replacements) {
                    $item = strtr(trim($m[0]), ['{' => '', '}' =>'']);
                    return array_key_exists($item, $replacements) ? $replacements[$item] : '';
                },
                $content
            );


    return $content;
}


function get_company_logo($size = NULL, $for_internal_use = NULL)
{   
    if($for_internal_use)
    {
        if($size == 'regular')
        {
            if(Config::get('constants.company_logo_internal'))
            {
                return asset(Storage::url(Config::get('constants.company_logo_internal')));
            }
        }
        else
        {
            if(Config::get('constants.company_logo_internal_small'))
            {
                return asset(Storage::url(Config::get('constants.company_logo_internal_small')));
            }
        }
    }
    else
    {
        if($size == 'regular')
        {
            if(Config::get('constants.company_logo'))
            {
                return asset(Storage::url(Config::get('constants.company_logo')));
            }
        }
        else
        {
            if(Config::get('constants.company_logo_small'))
            {
                return asset(Storage::url(Config::get('constants.company_logo_small')));
            }
        }
    } 

}

function get_favicon()
{
    if(Config::get('constants.favicon'))
    {
        return asset(Storage::url(Config::get('constants.favicon')));
    }
}

// check_customer_project_permission
function check_customer_project_permission($data_object, $key)
{
    if( (isset($data_object->{$key}) && $data_object->{$key} ) )         
    {
        return TRUE;
    }
}

function get_invoice_status_badge($status_id)
{
    $badges = [
        INVOICE_STATUS_PAID             => 'btn btn-outline-success',
        INVOICE_STATUS_UNPAID           => 'btn btn-outline-danger',
        INVOICE_STATUS_PARTIALLY_PAID   => 'btn btn-outline-info',
        INVOICE_STATUS_OVER_DUE         => 'btn btn-outline-warning',
        INVOICE_STATUS_CANCELED         => 'btn btn-outline-secondary',
        INVOICE_STATUS_DRAFT            => 'btn btn-outline-dark',

    ];

    return (isset($badges[$status_id])) ? $badges[$status_id] : '';
    
}

function get_estimate_status_badge($status_id)
{
    $badges = [
        ESTIMATE_STATUS_DRAFT           => 'btn btn-outline-dark',
        ESTIMATE_STATUS_SENT            => 'btn btn-outline-primary',
        ESTIMATE_STATUS_EXPIRED         => 'btn btn-outline-info',
        ESTIMATE_STATUS_DECLINED        => 'btn btn-outline-danger',
        ESTIMATE_STATUS_ACCEPTED        => 'btn btn-outline-success',
        

    ];

    return (isset($badges[$status_id])) ? $badges[$status_id] : '';
    
}

function log_activity($performedOn, $description, $value_to_save = NULL, $log_name = NULL)
{
    $activity = activity($log_name)
    ->performedOn($performedOn)
   ->causedBy(auth()->user())
   ->withProperties(['item' => $value_to_save])       
   ->log($description)
   ;


}

// Check User Permission
function check_perm($permission)
{
    $user_perm = config('constants.user_permissions');

    $user_perm = ($user_perm) ?? [];

    if(isset(auth()->user()->is_administrator) && auth()->user()->is_administrator)
    {
        return TRUE;
    }
    elseif (isset(auth()->user()->customer_id) && auth()->user()->customer_id)
    {
        return TRUE;
    }
    elseif(is_array($permission))
    {
        return array_intersect($permission ,  $user_perm);
    }   
    else
    {
        return (in_array($permission, $user_perm )) ? TRUE : FALSE;  
        
    } 
}

function is_menu_enable($menu)
{
    if(auth()->user()->is_administrator)
    {
        return TRUE;
    }
    else
    {
        $permissions = config('constants.user_permissions');     

       if(is_array($permissions) && count($permissions) > 0)
       {
            if(is_array($menu))
            {
                foreach ($menu as $m) 
                {
                   $matches  = preg_grep('/^'.$m.'(\w+)/i', $permissions ); 

                    if(count($matches) > 0)
                    {
                        return TRUE;
                    }
                }
                return FALSE;
            }
            else
            {
                $matches  = preg_grep('/^'.$menu.'(\w+)/i', $permissions ); 

                return (count($matches) > 0) ? TRUE : FALSE;
            }
            
       }       
    }

    return FALSE;    

}

function is_involved_in_project()
{    
    return config('constants.is_involved_in_project'); 
}
// 


function data_table_page_length()
{
    return config('constants.datatable_results_per_page');    
}

define("TICKET_THREAD_PAGE_LENGTH", 15 );

function get_payment_gateway_info($gateway_name)
{
    $payment_gateways = config('constants.payment_gateways');

    if($payment_gateways)
    {
        $payment_gateways = json_decode($payment_gateways);
        return (isset($payment_gateways->{$gateway_name})) ? $payment_gateways->{$gateway_name} : FALSE;        
    }
    return FALSE;
    
}


function calculate_invoice_amount_due($invoice_total, $amount_paid, $applied_credits)
{
    return ($invoice_total - ( $amount_paid + $applied_credits ) );
}

function convert_amount_to_lowest($amount)
{
    return bcmul($amount , 100);
}


function is_pusher_enable()
{
   return config('constants.is_pusher_enable');
}

function push_notification($user_id)
{
    if(is_pusher_enable())
    {
      $pusher = json_decode(config('constants.pusher'));
      

      $chanel = 'chanel_'.$user_id;

       $options = array(
        'cluster' => ($pusher->app_cluster) ? $pusher->app_cluster : 'mt1',
        'useTLS' => true
      );
      $pusher = new Pusher\Pusher(
        $pusher->app_key,
        $pusher->app_secret,
        $pusher->app_id,
        $options
      );

      $data['message'] = '';
      $pusher->trigger($chanel, 'new.notification', $data);
    }

    
}

function get_pusher_api_info()
{
    return json_decode(config('constants.pusher'));
}

function gen_url_for_attachment_download($filename)
{
    return route('attachment_download_link', Illuminate\Support\Facades\Crypt::encryptString($filename));
}

function upload_button($formId, $short_code_input_id = NULL)
{

?>

<div>
   <ul class="list-group" id="list_of_attachments"></ul>
   <div id="uploading_on_progress" style="display: none; text-align: center; font-size: 12px;"><?php echo __('form.uploading'); ?></div>
</div>
<br>
<div class="form-group">
   <input type="file" name="attachment" id="attachment" data-form-id="#<?php echo $formId; ?>" data-short-code-input-id="<?php echo $short_code_input_id; ?>" style="display:none;"/> 
   <a href="#" class="btn btn-light upload_link"><i class="fas fa-paperclip "></i> <?php echo __('form.upload_attachment'); ?></a>  
</div>

<?php
}


function tinyMceJsSript($selector)
{
    ?>
    tinymce.init({
                selector: '<?php echo $selector; ?>',
                setup : function(editor){
                     editor.on('change', function () {
                        tinymce.triggerSave();
                    });
                },
                 branding: false,
                 relative_urls: false,
                convert_urls: false,
                remove_script_host : false,
             
                height: "auto",
                autoresize_min_height: 220,
                autoresize_max_height: "auto",
                plugins: [
                            "advlist autolink lists link image charmap hr anchor pagebreak",
                            "wordcount visualblocks visualchars code fullscreen",
                            "nonbreaking save table contextmenu",
                            "paste textcolor colorpicker autoresize"
                        ],
                toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link",
                        toolbar2: "forecolor backcolor",

        });

    <?php
}


function tinyMceJsSriptWithFileUploader($selector)
{
    ?>
    tinymce.init({
                selector: '<?php echo $selector; ?>',
                branding: false,
                setup : function(editor){
                     editor.on('change', function () {
                        tinymce.triggerSave();
                    });
                },             
                height: "auto",
                autoresize_min_height: 220,
                autoresize_max_height: "auto",
                plugins: [
                            "advlist autolink lists link image charmap hr anchor pagebreak",
                            "wordcount visualblocks visualchars code fullscreen",
                            "nonbreaking save table contextmenu",
                            "paste textcolor colorpicker autoresize"
                        ],
                toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
                        toolbar2: "media | forecolor backcolor",
                        image_advtab: true,
                        // FileManager Part
                        relative_urls: false,
                        file_browser_callback : function(field_name, url, type, win) {
                          var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                          var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;

                          var cmsURL = "<?php echo url('/') ?>/" + 'laravel-filemanager?field_name=' + field_name;
                          if (type == 'image') {
                            cmsURL = cmsURL + "&type=Images";
                          } else {
                            cmsURL = cmsURL + "&type=Files";
                          }

                          tinyMCE.activeEditor.windowManager.open({
                            file : cmsURL,
                            title : 'Filemanager',
                            width : x * 0.8,
                            height : y * 0.8,
                            resizable : "yes",
                            close_previous : "no"
                          });
                        }

        });

    <?php
}

function gen_progress_bar($color, $value , $id = NULL) {
?>
<div class="progress">

    <div id="<?php echo $id; ?>" class="progress-bar <?php echo  $color; ?>" role="progressbar" style="width:<?php echo  $value; ?>%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"><?php echo  $value; ?>%</div>

</div>
<?php }

function gen_team_member_short_code($name)
{
    return str_slug($name).'-'.uniqid();
}

function is_recaptcha_enable()
{
    return (config('constants.enable_google_recaptcha') && config('constants.google_recaptcha_secret_key') && config('constants.google_recaptcha_site_key') ) ? TRUE: FALSE;
    //return FALSE;
    
}


function google_recaptcha($errors)
{
    if(is_recaptcha_enable()) { ?>   

        <div class="form-group">   

        <div data-size="invisible" data-callback="setResponse" id="recaptcha" class="g-recaptcha" 
        class="g-recaptcha<?php echo $errors->has('g-recaptcha-response') ? ' has-error' : '' ?>"
                  data-sitekey="<?php echo config('constants.google_recaptcha_site_key'); ?>">
        </div>
        <div class="invalid-feedback d-block"><?php echo  $errors->first('g-recaptcha-response') ?></div>
        </div>

    <?php }

    
}


function is_support_feature_disabled()
{
    $support_configuration = config('constants.support_configuration') ;

    if($support_configuration)
    {
        $config = json_decode($support_configuration);
        return (isset($config->disable_support) && $config->disable_support) ? TRUE : FALSE;
    }
    return FALSE;
}

function is_customer_registration_feature_disabled()
{
    $support_configuration = config('constants.customer_configuration') ;

    if($support_configuration)
    {
        $config = json_decode($support_configuration);
        return (isset($config->disable_customer_registration) && $config->disable_customer_registration) ? TRUE : FALSE;
    }
    return FALSE;
}

function is_current_user($record_user_id)
{
   
    return (auth()->user()->id == $record_user_id) ? TRUE : FALSE;
}

function is_current_user_a_customer()
{
   
    return (isset(auth()->user()->customer_id) && auth()->user()->id) ? TRUE : FALSE;
}

function is_current_user_a_team_member()
{
   
    return (isset(auth()->user()->customer_id) && auth()->user()->id) ? FALSE : TRUE;
}




function is_knowledge_base_feature_disabled()
{
    $support_configuration = config('constants.support_configuration') ;

    if($support_configuration)
    {
        $config = json_decode($support_configuration);

        if(isset($config->disable_knowledge_base) && $config->disable_knowledge_base)
        {
            return TRUE;
        }
        else
        {
            if(isset($config->knowledge_base_is_private) && $config->knowledge_base_is_private)
            {
                return (auth()->check()) ? FALSE : TRUE;                
            }
        }        

    }
    return FALSE;
}

    function knowledge_base_breadcrumb(array $breadcrumb_for)
    {        
        if(isset($breadcrumb_for['article']))
        {
            $article = $breadcrumb_for['article'];
        }

        if(isset($breadcrumb_for['group']))
        {
            $group = $breadcrumb_for['group'];
        }


        $url[0] = ['name' => __('form.knowledge_base'), 'url' => route('knowledge_base_home') ];        

        if(isset($group))
        {   
            if(isset($group->parent) && !empty($group->parent))
            {
                $url[] = ['name' => $group->parent->name , 'url' => route('knowledge_base_category_customer_view', $group->parent->slug) ];
            }            

            $url[] = ['name' => $group->name , 'url' => route('knowledge_base_category_customer_view', $group->slug) ];
        }

        else if(isset($article))
        {
            $group = \App\ArticleGroup::with('parent')->find($article->group->id);

            if(($group->count() > 0) && isset($group->parent) && !empty($group->parent))
            {
                foreach ($group->parent as $parent_group) 
                {
                    $url[] = ['name' => $parent_group->name , 'url' => route('knowledge_base_category_customer_view', $parent_group->slug) ];
                }
            }

            $url[] = ['name' => $group->name , 'url' => route('knowledge_base_category_customer_view', $group->slug) ];
        }
      

        if(is_countable($url) && count($url) > 0)
        {
            ?>

            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <?php foreach($url as $page) {?>
                    <li class="breadcrumb-item"><a href="<?php echo $page['url']; ?>"><?php echo $page['name']; ?></a></li>
                <?php } ?>    
              </ol>
            </nav>

            <?php
        }

    }

function get_setting($key)
{
    return \App\Setting::get_setting($key);
}


function calculate_tax_total($taxes, $amount_column = 'amount')
{
    if(!empty($taxes))
    {
        return (is_array($taxes)) ? array_sum(array_column($taxes, $amount_column)) : 0 ;
    }
    else
    {
        return 0;
    }
    
}

function get_languges()
{
    return [
            'en'        => 'English',
            'ca'        => 'Catalan',
            'zh-CN'     => 'Chinese',
            'fr'        => 'French',
            'de'        => 'German',
            'es'        => 'Spanish',       
            'id'        => 'Indonesia',
            'it'        => 'Italian',
            'nl'        => 'Dutch',
            'ja'        => 'Japanese',
            'pl'        => 'Polish',   
            'pt'        => 'Portuguese',
            // 'pt-BR'     => 'Portuguese (Brazilian)',
            'ro'        => 'Romanian',
            'ru'        => 'Russian',
            'sk'        => 'Slovak',
            'sv'        => 'Swedish',
            'tr'        => 'Turkish',
            'vi'        => 'Vietnamese',
           
        ];
}


function active_menu($menu)
{
    $prefix = str_replace('/', '', Request()->route()->getPrefix() ) ;

   if(is_array($menu))
   {
        return (in_array( $prefix , $menu)) ? 'active' : '';
   }
   else
   {
       if($menu == 'dashboard' && $prefix == '')
       {
        return 'active';
       }

       return ($prefix == $menu) ? 'active' : '';
   }
}

function add_action($name, $callback)
{
    Eventy::addAction($name,$callback);
}

function add_filter($name, $callback)
{
    Eventy::addAction($name,$callback);
}

function load_extended_files($config_key)
{
    $files = config('microelephant.'.$config_key);

    if(is_array($files) && !empty($files))
    {
        foreach ($files as $file) 
        {
            if($config_key == 'admin_css' || $config_key == 'customer_css')
            {
              ?><link rel="stylesheet" href="<?php  echo $file ?>"><?php  
            }
            else
            {                    
                ?><script type="text/javascript" src="<?php  echo $file ?>"></script><?php  
            }
        }
    }
}

function add_http($url) 
{
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) 
    {
        $url = "http://" . $url;
    }
    return $url;
}

function get_software_version()
{
    return '2.0';
}

// Needs to be at the bottom of the file
include_once 'profile_photo_upload_helper.php';
include_once 'task_helper.php';
include_once 'reminder_helper.php';




