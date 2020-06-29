<?php

//function convert_to_money_from_number($number)
//{
//    $money_format_options   = config('constants.money_format');
//
//    return number_format((float)$number, $money_format_options->currency->precision , '.', '');
//}

function format_currency($input, $with_currency = FALSE, $cur_symbol = NULL)
{

    $method                 = config('constants.digit_grouping_method');
    $money_format_options   = config('constants.money_format');



    if($method == config('constants.FORMAT_CURRENCY_METHOD_ONE')  )
    {
        $num_of_digits_to_separate_from_last_part   = 3;
        $num_of_digits_for_grouping                 = 3;
    }
    elseif($method == config('constants.FORMAT_CURRENCY_METHOD_TWO') )
    {
        $num_of_digits_to_separate_from_last_part   = 3;
        $num_of_digits_for_grouping                 = 2;
    }
    elseif($method == config('constants.FORMAT_CURRENCY_METHOD_THREE') )
    {
        $num_of_digits_to_separate_from_last_part   = 4;
        $num_of_digits_for_grouping                 = 4;
    }


    $round_precision            = $money_format_options->currency->precision ;
    $decimal_symbol             =  $money_format_options->currency->decimal ;
    $digit_grouping_symbol      =  $money_format_options->currency->thousand ;

    $val = format_currency_helper($input, $round_precision, $decimal_symbol, $digit_grouping_symbol, $num_of_digits_to_separate_from_last_part, $num_of_digits_for_grouping);

    $symbol = ($cur_symbol) ? $cur_symbol : trim($money_format_options->currency->symbol) ;
    
    if($val)
    {
        return ($with_currency) ? $symbol . $val : $val;
    }
    elseif($val == 0)
    {
        return ($with_currency) ? $symbol . __('form.num_value_0') : __('form.num_value_0');
     
    }
    else
    {
        return "";
    }

}



/*
 * Currency Formatting Types
    Style A: 10,000,000,000 // Most currencies
    Style B: 10,00,00,00,000 // South East Asian
    Style C: 100,0000,0000 // Japan, China
 */

// Covers Most currencies in the world
function format_currency_helper($input, $round_precision, $decimal_symbol, $digit_grouping_symbol, $num_of_digits_to_separate_from_last_part, $num_of_digits_for_grouping)
{
    $is_negative = false;
    if($input < 0)
    {
        $is_negative = true;
        $input = abs($input);
    }
    //CUSTOM FUNCTION TO GENERATE ##,##,###.##
    $dec = "";
    $pos = strpos($input, $decimal_symbol );

    if ($pos != false)
    {
        //decimals
        $dec = substr(number_format(substr($input,$pos), $round_precision ),1);
        $input = substr($input,0,$pos);
    }

    $num = substr($input,-$num_of_digits_to_separate_from_last_part); //get the last 3 digits
    $input = substr($input,0, -$num_of_digits_to_separate_from_last_part); //omit the last 3 digits already stored in $num

    while(strlen($input) > 0) //loop the process - further get digits 2 by 2
    {
        $num = substr($input,-$num_of_digits_for_grouping).$digit_grouping_symbol.$num;
        $input = substr($input,0,-$num_of_digits_for_grouping);
    }
    $a = $num . $dec;

    return ($is_negative == true) ? "-".$a : $a ;

}