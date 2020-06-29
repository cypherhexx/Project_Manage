<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Expense extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id')->withTrashed();
    }

    function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }


    public function payment_mode()
    {
        return $this->belongsTo(PaymentMode::class, 'payment_mode_id')->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class)->withTrashed();
    }


    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class)->withTrashed();
    }




    public static function dropdown_expenses()
    {
        $nothing_selected = __('form.nothing_selected');
        $select = __('form.dropdown_select_text');

        $data['customer_id_list'] = [];
        $data['project_id_list'] = [];
        $data['categories'] = array('' => $nothing_selected) + ExpenseCategory::pluck('name', 'id')->toArray();
        $data['currency_id_list'] = array('' => $nothing_selected ) + Currency::orderBy('code', 'ASC')->pluck('code', 'id')->toArray();
        $data['payment_mode_id_list'] = array('' => $nothing_selected ) + PaymentMode::orderBy('name', 'ASC')
        ->whereNULL('inactive')->pluck('name', 'id')->toArray();

        $data['vendor_id_list'] = array('' => $nothing_selected) + Vendor::pluck('name', 'id')->toArray();


        // Tax Information
        $data['tax_id_list']  = Tax::orderBy('name','ASC')
            ->select(
                DB::raw("CONCAT(name,' - ',rate , '%') AS name") , "display_as AS id")->pluck('name', 'id')->toArray();


        // End of Tax Information


        return $data;
    }

    public static function calculate_amount_after_tax($taxes, $amount)
    {
        $amt = $amount;

        if(is_array($taxes) && count($taxes) > 0)
        {
            foreach ($taxes as $tax)
            {
                $tax_string_array = explode("_", $tax);

                $rate = $tax_string_array[0];

                $amt += number_format((($amount * $rate)/100), 2, '.', '');
            }
        }
        return $amt;
    }
}
