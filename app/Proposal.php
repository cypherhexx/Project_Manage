<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class Proposal extends Model {

    use SoftDeletes;
    use \App\Traits\TagOperation;
 


    protected $dates = ['deleted_at'];

   

    protected $fillable = [
        'url_slug', 'number', 'title', 'content', 'component_id', 'component_number',
        'date', 
        'open_till' ,
        'currency_id', 
        'discount_type_id',
        'status_id',
        'assigned_to',
        'send_to',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country_id',
        'zip_code',
        'show_quantity_as',
        'sub_total',
        'discount_method_id',
        'discount_rate',
        'discount_total',
        'taxes',
        'tax_total',
        'adjustment' ,
        'total' ,
        'created_by' 
    ];

    

    function country()
    {
        return $this->belongsTo(Country::class ,'country_id','id');
    }
    

    function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    function get_currency_symbol()
    {
        return ($this->currency_id && isset($this->currency->symbol)) ? $this->currency->symbol : NULL ;
    }

    function status()
    {
        return $this->belongsTo(ProposalStatus::class ,'status_id','id');
    }

    
    function item_line()
    {
        return $this->hasMany(ProposalItem::class);
    }


    static function dropdown($task_id = NULL)
    {

        $select = __('form.dropdown_select_text');

        $data['component_number_options'] = [];

        $data['assigned_to_list'] = array('' => $select) + User::activeUsers()
                ->select(
                    DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->pluck('name', 'id')->toArray();


        $data['status_id_list'] = ProposalStatus::orderBy('id','ASC')->pluck('name', 'id')->toArray();



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

        $data['component_id_list'] = array('' => $select) + Component::orderBy('name','ASC')
                ->whereIn('id', [COMPONENT_TYPE_LEAD, COMPONENT_TYPE_CUSTOMER])->pluck('name', 'id')->toArray();


        $data['country_id_list'] = ["" => __('form.nothing_selected')]  + Country::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();


        return $data;
    }


    public function related_to()
    {

        if($this->component_id == COMPONENT_TYPE_LEAD)
        {
            return $this->belongsTo(Lead::class,'component_number','id')->withTrashed();
        }
        elseif ($this->component_id == COMPONENT_TYPE_CUSTOMER)
        {
            return $this->belongsTo(Customer::class,'component_number','id')->withTrashed();
        }

    }

    // Need the following function when converting proposal to Estimate or Invoice
    public function customer()
    {

        if($this->component_id == COMPONENT_TYPE_LEAD)
        {
            if(isset($this->related_to->customer_id) && $this->related_to->customer_id)
            {
                return $this->belongsTo(Lead::class,'component_number','id')
                    ->leftJoin('customers', 'customers.id', '=', 'leads.customer_id')
                    ->select('customers.id AS id', 'customers.name AS name');
            }


        }
        else
        {
            return $this->belongsTo(Customer::class,'component_number','id');
        }



    }

    static function proposal_short_codes()
    {
        return [           
            ''                              => __('form.select_short_code'),
            '{proposal_items}'              => __('form.items_list'),
            '{proposal_number}'             => __('form.proposal_number'),
            '{proposal_title}'              => __('form.proposal_title'),
            '{proposal_total}'              => __('form.proposal_total'),
            '{proposal_subtotal}'           => __('form.proposal_subtotal'),
            '{proposal_open_till}'          => __('form.proposal_open_till_date'),
            '{proposal_proposal_to}'        => __('form.proposal_proposal_to'),
            '{proposal_address}'            => __('form.proposal_address'),
            '{proposal_city}'               => __('form.proposal_city'),
            '{proposal_state}'              => __('form.proposal_state'),
            '{proposal_zip}'                => __('form.proposal_zip_code'),
            '{proposal_country}'            => __('form.proposal_country'),
            
        ];
    }
}