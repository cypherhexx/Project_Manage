<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerGroup extends Model {

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    function customers()
    {
        return $this->belongsToMany(Customer::class ,'tag_customers_groups','group_id','customer_id');
    }


}