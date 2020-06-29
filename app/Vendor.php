<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

use App\NumberGenerator;

class Vendor extends Model
{
	use SoftDeletes;



    
    protected $fillable = [
       'number' ,'name', 'phone', 'website', 
       'address', 'city', 'state', 'zip_code', 'country_id', 'notes',       
       'contact_first_name', 'contact_last_name', 'contact_email', 'contact_phone', 'contact_position'
    ];

    
    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            
            $query->number      = NumberGenerator::gen(COMPONENT_TYPE_VENDOR);
            $query->created_by  = auth()->user()->id;

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


    public static function statistics()
    {
        $stat = [
            'vendor_active'     => 0,
            'vendor_inactive'   => 0,
            
        ];

        $vendor = Vendor::select( DB::raw('IFNULL(inactive, 0) as inactive'), DB::raw('count(*) as total'))
            ->groupBy('inactive')
            ->pluck('total','inactive')->all();

       

        if(count($vendor) > 0)
        {
            if(isset($vendor[0]))
            {
                $stat['vendor_active'] = $vendor[0];
            }

            if(isset($vendor[1]))
            {
                $stat['vendor_inactive'] = $vendor[1];
            }
        }

       

        return $stat;

    }

    function country()
    {
        return $this->belongsTo(Country::class ,'country_id','id');
    }

    static function dropdowns()
    {
    
        $data['country_id_list'] = ["" => __('form.nothing_selected')]  + Country::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();

        return $data;
    }
}
