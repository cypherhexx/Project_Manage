<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Payment;
use App\NumberGenerator;
use App\PaymentApiResponse;
use App\Notifications\PaymentReceived;
use App\User;
use App\Currency;
use App\Services\PaymentGateway\Contracts\PaymentBean;

class Invoice extends Model
{
    use SoftDeletes;
    use \App\Traits\TagOperation;

    protected $dates = ['deleted_at'];

    //  protected $casts = [
    //     'taxes' => 'array',
    // ];

    protected $fillable = [

            'url_slug', 'number','reference','customer_id','project_id',      
            
            // Billing Address
            'address',
            'city',
            'state',
            'country_id',
            'zip_code',
            // Shipping Address
            'shipping_address',
            'shipping_city',
            'shipping_state',
            'shipping_zip_code',
            'shipping_country_id',

            'currency_id',
            'discount_type_id',
            'status_id',
            'sales_agent_id',
            'admin_note',
            'client_note',
            'terms_and_condition',
            'date',
            'due_date',
            'show_quantity_as',
            'sub_total',
            'discount_method_id',
            'discount_rate',
            'discount_total',
            'taxes',
            'tax_total',
            'adjustment',
            'total',
            'created_by',
            'allow_partial_payment',

    ];

    function children()
    {
        return $this->belongsToMany(Invoice::class ,'child_invoices','parent_invoice_id', 'child_invoice_id');
    }


    function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    function get_currency_symbol()
    {
        return ($this->currency_id && isset($this->currency->symbol)) ? $this->currency->symbol : NULL ;
    }


    function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    function project()
    {
        return $this->belongsTo(Project::class);
    }

    function payments()
    {
        return $this->hasMany(Payment::class);
        
    }

    function sales_agent()
    {
        return $this->belongsTo(User::class, 'sales_agent_id', 'id')
            ->select(DB::raw("CONCAT(first_name,' ',last_name) AS name"))->withTrashed();
    }
   
    

    function status()
    {
        return $this->belongsTo(InvoiceStatus::class ,'status_id','id');
    }

    function get_tags_as_badges($with_line_break = NULL)
    {
        $tags = $this->tags;
        $line_break = ($with_line_break) ? '<br>' : '';
        
        if(!empty($tags))
        {
            $tag_list = array_column($tags->toArray(), 'name');
            $str = "";
            foreach ($tag_list as $item)
            {
                $str .='<span class="badge badge-light">'.$item.'</span>'.$line_break;
            }
            return $str;
        }


    }

    function person_created()
    {
        return $this->belongsTo(User::class ,'created_by','id')->withTrashed();
    }

    function item_line()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    static function sales_agent_dropdown()
    {       

        return User::activeUsers()
                ->select(
                    DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->pluck('name', 'id')->toArray();
    }

    static function dropdown()
    {

        $select                         = __('form.dropdown_select_text');

        $data['customer_id_list']       = [];
        $data['project_id_list']        = [];
        $data['sales_agent_id_list']    = array('' => $select) +  self::sales_agent_dropdown();


        // Tax Information
        $taxes  = Tax::orderBy('name','ASC')
            ->select(
                DB::raw("CONCAT(name,' ',rate , '%') AS name"),
                'rate', 'display_as', DB::raw("CONCAT(name,' ',rate , '%') AS text") )->get();

        $data['tax_id_list'] = [];

        if(count($taxes) > 0)
        {
            foreach ($taxes as $key=>$r)
            {

                $data['tax_id_list'][$key] = [
                    'id'    => $r->display_as ,
                    'name'  => $r->name,
                    'text'  => $r->text,
                    'rate'  => $r->rate
                ];
            }
        }
        // End of Tax Information





        $data['tag_id_list'] = Tag::orderBy('name','ASC')->pluck('name', 'id')->toArray();



        $data['currency_id_list'] = array('' => $select) + Currency::orderBy('code','ASC')
                ->select(
                    DB::raw("CONCAT(code,' : ',symbol) AS name"),'id')->pluck('name', 'id')->toArray();

        $data['discount_type_id_list'] = array('' => __('form.no_discount'),
            DISCOUNT_TYPE_BEFORE_TAX => __('form.before_tax'),
            DISCOUNT_TYPE_AFTER_TAX => __('form.after_tax'),
        );




        $data['country_id_list'] = ["" => __('form.select_country')]  + Country::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();


        return $data;
    }


    static function home_page_stat()
    {
        $data['percent']    = 0;
        $data['figure']     = '0 / 0';

        $rec = Invoice::select('status_id', DB::raw('count(*) as total'))
            ->whereIn('status_id', [INVOICE_STATUS_UNPAID, INVOICE_STATUS_PARTIALLY_PAID, INVOICE_STATUS_OVER_DUE ])
            ->groupBy('status_id')->pluck('total','status_id')->all(); 
      

        if(count($rec) > 0)
        {
            $total_number                           = array_sum($rec);
            $number_of_invoices_awaiting_payment    = (isset($rec[INVOICE_STATUS_UNPAID])) ? $rec[INVOICE_STATUS_UNPAID] : 0;

            $data['figure']                         = $number_of_invoices_awaiting_payment . " / ". $total_number;
            $data['percent']                        = round(($number_of_invoices_awaiting_payment/$total_number) * 100);
           
            
        }
        return $data;
    }

    static function stat($currency_id, $customer_id = NULL)
    {
        $paid_invoices  = Invoice::where('status_id', INVOICE_STATUS_PAID)
        ->selectRaw('IFNULL(sum(total), 0) as total')
        ->where('currency_id', $currency_id)
        ->whereBetween('updated_at', [Carbon::now()->subDays(30) , Carbon::now()] );

        $unpaid_invoices = Invoice::whereIn('status_id', [
            INVOICE_STATUS_UNPAID, 
            INVOICE_STATUS_PARTIALLY_PAID, 
            INVOICE_STATUS_OVER_DUE,
            INVOICE_STATUS_DRAFT,
        ])
        ->where('currency_id', $currency_id)
        ->selectRaw('count(id) AS number_of_records, status_id, (IFNULL(SUM(total), 0) - (IFNULL(SUM(amount_paid), 0) + IFNULL(SUM(applied_credits), 0) )) AS total_outstanding ')->groupBy('status_id');      


        if(!check_perm('invoices_view') && check_perm('invoices_view_own'))
        {
            $paid_invoices->where('created_by', auth()->user()->id);
            $unpaid_invoices->where('created_by', auth()->user()->id);
        }

        if($customer_id)
        {
            $paid_invoices->where('customer_id', $customer_id);
            $unpaid_invoices->where('customer_id', $customer_id);
        }

        $paid_invoices          = $paid_invoices->groupBy('status_id')->get()->first(); 

        $data['paid_invoices']  = ($paid_invoices && $paid_invoices->count() > 0) ? $paid_invoices->toArray()['total'] : 0;

        $invoice_statuses       = [
            INVOICE_STATUS_UNPAID           => 0, 
            INVOICE_STATUS_PARTIALLY_PAID   => 0, 
            INVOICE_STATUS_OVER_DUE         => 0, 
            INVOICE_STATUS_DRAFT            => 0, 
        ];

        
        $unpaid_invoices        = $unpaid_invoices->get();


       $total                   = 0;
       $total_outstanding       = 0;

        if(count($unpaid_invoices) > 0)
        {
            foreach ($unpaid_invoices as $unpaid_invoice) 
            {
                $invoice_statuses[$unpaid_invoice->status_id] = $unpaid_invoice->number_of_records;
                $total_outstanding   += $unpaid_invoice->total_outstanding;
            }

            $total = array_sum($invoice_statuses);
        }

        
        // Calculate the Percentage 
        foreach ($invoice_statuses as $key => $value) 
        {
            $invoice_statuses[$key] = [
                'percent'   => ($total == 0) ? 0 : round(($value / $total) * 100), 
                'number'    => $value           
            ];
        }

        $data['stat_unpaid_invoices']       = $invoice_statuses;
     
        $data['stat_total_unpaid_invoices'] = $total;

        $data['total_outstanding']          = $total_outstanding;        

        return $data;
    }

    function get_currency()
    {
         // Get the currency iso code and symbol
        if(isset($this->currency->code))
        {
            $data['symbol']    = $this->currency->symbol;
            $data['iso']       = $this->currency->code;
        }
        else
        {
            $currency          = Currency::default()->get()->first();
            $data['symbol']    = $currency->symbol;
            $data['iso']       = $currency->code;
        }

        return $data;        
    }

    function validate_payment_amount($amount_submitted)
    {
        $amount_due    = calculate_invoice_amount_due($this->total, $this->amount_paid, $this->applied_credits);
           
        if($this->allow_partial_payment)
        { 
            $amount  = $amount_submitted ;
        }
        else
        {           

            // If partial payment is not allowed then check the sent amount from the client side to see if its less than the amount due
            if($amount_submitted < $amount_due)
            {
               return FALSE;
            }

            $amount        =  $amount_due;
            
        }            

        return $amount;
    }

    // update_invoice_and_insert_in_payment_table
    public function payment_received(PaymentBean $paymentBean)        
    {     

        $invoice                    = $this;
        $amount                     = $paymentBean->getAmount();

        $payment                    = new Payment();              
        $payment->number            = NumberGenerator::gen(COMPONENT_TYPE_PAYMENT);
        $payment->date              = date2sql($paymentBean->getDate());
        $payment->invoice_id        = $invoice->id ;
        $payment->amount            = $amount ;                
        $payment->payment_mode_id   = $paymentBean->getPayment_mode_id() ;
        $payment->transaction_id    = $paymentBean->getReference();
        $payment->note              = $paymentBean->getNote();
        $payment->entry_by          = (isset(auth()->user()->id)) ? auth()->user()->id : NULL;
        $payment->save();


        // Update Invoice and Insert in Payments Table    
        $invoice->amount_paid = $invoice->amount_paid + $amount ; 


        // Update Invoice Table
        if(( $invoice->amount_paid + $invoice->applied_credits) >= $invoice->total)
        {
            $invoice->status_id = INVOICE_STATUS_PAID;
        }
        else
        {
            $invoice->status_id = INVOICE_STATUS_PARTIALLY_PAID;
        }

        $invoice->save();


        if($paymentBean->getApi_response())
        {
            // Create Log in Payment API Response table
            PaymentApiResponse::create([                    
                'payment_id'        => $payment->id,
                'data'              => json_encode($paymentBean->getApi_response())
            ]);
        }
                

        try{

            $currency           = $this->get_currency();
            $formatted_amount   = format_currency($amount, TRUE, $currency['symbol']);        

            // Send Notification to person who created the invoice
            $member = User::find($invoice->created_by);
            
            if($member)
            {                                
                $member->notify(new PaymentReceived($invoice->id, $invoice->number, $formatted_amount , $payment->id ));
            }
            
            // Notify Customer 
            $primary_contact = $invoice->customer->primary_contact;
            
            if($primary_contact)
            {
                $primary_contact->notify(new PaymentReceived($invoice->id, $invoice->number, $formatted_amount , $payment->id ));
            }
        }
        catch (\Exception  $e) {

        }

        return $payment;
    }
 
}
