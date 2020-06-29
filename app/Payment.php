<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{

    function payment_mode()
    {
        return $this->belongsTo(PaymentMode::class, 'payment_mode_id', 'id');
    }

    function invoice()
    {
    	return $this->belongsTo(Invoice::class);
    }

}
