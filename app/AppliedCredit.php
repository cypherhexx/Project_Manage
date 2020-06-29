<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppliedCredit extends Model
{
    function invoice()
    {
        return $this->belongsTo(Invoice::class)->withTrashed();
    }

    
    function credit_note()
    {

        return $this->belongsTo(CreditNote::class)->withTrashed();
    }
}
