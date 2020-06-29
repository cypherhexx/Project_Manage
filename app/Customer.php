<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

use App\NumberGenerator;

class Customer extends Model {

    use SoftDeletes;
   
    
    protected $dates = ['deleted_at'];

   protected $fillable = [
       'number' ,'name', 'vat_number', 'phone', 'website', 
       'address', 'city', 'state', 'zip_code', 'country_id', 
       'shipping_is_same_as_billing', 
       'shipping_address', 'shipping_city', 'shipping_state', 'shipping_zip_code', 'shipping_country_id',
       'notes', 'default_language', 'currency_id', 'created_by'
    ];

    
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            $query->number          = NumberGenerator::gen(COMPONENT_TYPE_CUSTOMER);
            $query->created_by      = (isset(auth()->user()->id)) ? auth()->user()->id : NULL;
            $query->currency_id     = $query->currency_id ?? config('constants.default_currency_id');


            if($query->shipping_is_same_as_billing)
            {
                // // Shipping Address
                $query->shipping_address            = $query->address;
                $query->shipping_city               = $query->city;
                $query->shipping_state              = $query->state;
                $query->shipping_zip_code           = $query->zip_code;
                $query->shipping_country_id         = $query->country_id;
            }


        });

        static::updating(function ($query) {
            $query->currency_id     = $query->currency_id ?? config('constants.default_currency_id');

            if($query->shipping_is_same_as_billing)
            {
                // // Shipping Address
                $query->shipping_address            = $query->address;
                $query->shipping_city               = $query->city;
                $query->shipping_state              = $query->state;
                $query->shipping_zip_code           = $query->zip_code;
                $query->shipping_country_id         = $query->country_id;
            }
        });
    }


    protected static $logAttributes = ['id', 'name'];


    function groups()
    {
        return $this->belongsToMany(CustomerGroup::class ,'tag_customers_groups','customer_id','group_id');
    }

    function country()
    {
        return $this->belongsTo(Country::class ,'country_id','id');
    }

    function shipping_country()
    {
        return $this->belongsTo(Country::class ,'shipping_country_id','id');
    }

    public function primary_contact()
    {
        return $this->hasOne(CustomerContact::class)->where('is_primary_contact', '=', 1);
            
    }

    function contacts()
    {
        return $this->hasMany(CustomerContact::class);
    }

    function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    function language()
    {
        return $this->belongsTo(Language::class, 'default_language_id');
    }

    function projects()
    {
        return $this->hasMany(Project::class);
    }

    function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    function billable_expenses()
    {
        return $this->expenses()->where('is_billable', TRUE)->whereNull('invoice_id');
    }



    public static function statistics()
    {
        $stat = [
            'customer_active' => 0,
            'customer_inactive' => 0,
            'contact_active' => 0,
            'contact_inactive' => 0,
        ];

        $customer = Customer::select( DB::raw('IFNULL(inactive, 0) as inactive'), DB::raw('count(*) as total'))
            ->groupBy('inactive')
            ->pluck('total','inactive')->all();

        $contact = CustomerContact::select(DB::raw('IFNULL(inactive, 0) as inactive') , DB::raw('count(*) as total'))
            ->groupBy('inactive')
            ->pluck('total', 'inactive')->all();

        if(count($customer) > 0)
        {
            if(isset($customer[0]))
            {
                $stat['customer_active'] = $customer[0];
            }

            if(isset($customer[1]))
            {
                $stat['customer_inactive'] = $customer[1];
            }
        }

        if(count($contact) > 0)
        {
            if(isset($contact[0]))
            {
                $stat['contact_active'] = $contact[0];
            }

            if(isset($contact[1]))
            {
                $stat['contact_inactive'] = $contact[1];
            }
        }

        return $stat;

    }

    static function dropdowns()
    {
        $data['currency_id_list'] = ["" => __('form.system_default')]  + Currency::orderBy('code', 'ASC')->pluck('code', 'id')->toArray();
        $data['default_language_id_list'] = ["" => __('form.system_default')]  +  get_languges() ;
        $data['group_id_list']      = CustomerGroup::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();
        $data['country_id_list'] = ["" => __('form.nothing_selected')]  + Country::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();

        return $data;
    }
    

    function get_groups_as_badges($with_line_break = NULL)
    {
        $tags = $this->groups;
        $line_break = ($with_line_break) ? '<br>' : '';
        if(count($tags) > 0)
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


    static function column_sequence_for_import()
    {
        return [

            // Contact Person
           'A' => 'first_name', 
           'B' => 'last_name', 
           'C' => 'email', 
           'D' => 'contact_person_phone', 
           'E' => 'position',     


            // Company Information
           'F' => 'name', 
           'G' => 'phone', // company Phone
           'H' => 'vat_number', 
           'I' => 'website',   


           // Billing Address
           'J' => 'address', 
           'K' => 'city', 
           'L' => 'state', 
           'M' => 'zip_code', 
           'N' => 'country',

           // Shipping Address
           'O' => 'shipping_address', 
           'P' => 'shipping_city', 
           'Q' => 'shipping_state', 
           'R' => 'shipping_zip_code', 
           'S' => 'shipping_country',
           
        ];
    }


    public function delete_has_many_relations($relations)
    {
        if(is_array($relations) && count($relations) > 0)
        {
            foreach($relations as $relation) 
            {
                $relation = $this->{$relation}()->get();

                if(count($relation) > 0)
                {
                    foreach ($relation as $r) 
                    {
                       $r->forcedelete();
                    }
                } 
            
                
            }
        }
    }
    
    function credit_notes()
    {
        return $this->hasMany(CreditNote::class);
    }

    function available_credits()
    {
        return $this->credit_notes()
        ->select(DB::raw('IFNULL(sum(total - IFNULL(amount_credited, 0)), 0) AS amount'))
        ->where('status_id', CREDIT_NOTE_STATUS_OPEN)->get()->first();
    }

    function get_records_for_statement($customer_id, $date_from, $date_to)
    {
        $sql = " SELECT * FROM (
            (SELECT created_at, 'invoice' AS type, id, number, date, total AS amount, due_date AS info_1, url_slug AS info_2 FROM invoices WHERE customer_id = ? AND date BETWEEN ? AND ?        
            )

            UNION All

           (SELECT payments.created_at, 'payment' AS type, payments.id AS id , payments.number AS number, payments.date AS date, payments.amount AS amount, 
            invoices.number AS info_1, invoice_id AS info_2 FROM payments
            LEFT JOIN invoices ON payments.invoice_id = invoices.id 
            WHERE customer_id = ? AND payments.date BETWEEN ? AND ?
            )

            UNION All

            (SELECT credit_notes.created_at, 'credit_note' AS type, id, number, date, total AS amount, '' AS info_1, '' AS info_2 FROM credit_notes WHERE customer_id = ? AND date BETWEEN ? AND ? )        
            
            UNION All

            (SELECT applied_credits.created_at, 'applied_credit' AS type, applied_credits.id AS id , credit_notes.number AS number, 
                applied_credits.date AS date, applied_credits.amount AS amount, 
                invoices.number AS info_1, invoice_id AS info_2 FROM applied_credits
                LEFT JOIN invoices ON applied_credits.invoice_id = invoices.id
                LEFT JOIN credit_notes ON applied_credits.credit_note_id = credit_notes.id  
                WHERE invoices.customer_id = ? AND applied_credits.date BETWEEN ? AND ?)
            
            
            ) a ORDER BY a.created_at ASC

        ";

        $records = DB::select($sql, [ 
            $customer_id, $date_from, $date_to, 
            $customer_id, $date_from, $date_to,
            $customer_id, $date_from, $date_to,
            $customer_id, $date_from, $date_to
         ]);


        return $records;
    }


    function get_beginning_balance_for_statement($customer_id, $date)
    {
        $sql = "SELECT IFNULL(SUM(amount), 0) AS beginning_balance FROM (
            (SELECT SUM(IFNULL(total, 0)) AS amount FROM invoices WHERE customer_id = ? AND date < ? )        

            UNION All

            (SELECT (-1 * IFNULL(SUM(payments.amount), 0)  ) AS amount FROM payments            
            LEFT JOIN invoices ON payments.invoice_id = invoices.id 
            WHERE customer_id = ? AND payments.date < ?
            )

            UNION All

            (SELECT (-1 * IFNULL(SUM(total), 0) ) AS amount FROM credit_notes WHERE customer_id = ? AND date < ? )        
                
            
            ) a 

        ";

        $records = DB::select($sql, [ 
            $customer_id, $date, 
            $customer_id, $date,
            $customer_id, $date,
            $customer_id, $date
         ]);


        return (count($records) > 0) ? $records[0]->beginning_balance : 0 ;
        
    }

}