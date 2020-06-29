<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EstimateItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
            'estimate_id', 'description', 'long_description', 'quantity', 'unit', 'rate', 'tax_id', 'sub_total'
    ];


    protected $casts = [
        'tax_id' => 'array',
    ];


}
