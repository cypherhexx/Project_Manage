<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerRegistration extends Model
{
    protected $fillable = [
       'name', 'vat_number', 'phone', 'website', 
       'address', 'city', 'state', 'zip_code', 'country_id', 
       'contact_first_name', 'contact_last_name', 'contact_email', 'contact_phone', 'contact_position','contact_password' ,
       'verification_token', 'verified'

    ];
}
