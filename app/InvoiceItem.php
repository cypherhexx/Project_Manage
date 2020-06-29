<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
            'invoice_id', 'description', 'long_description', 'quantity', 'unit', 'rate', 'tax_id', 'sub_total'
    ];

    protected $casts = [
        'tax_id' => 'array',
    ];


    function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

   

}
