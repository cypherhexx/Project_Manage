<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentApiResponse extends Model
{
   protected $fillable = ['payment_id', 'data'];
}
