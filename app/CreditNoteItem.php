<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditNoteItem extends Model
{
     public $timestamps = false;

    protected $fillable = [
            'credit_note_id', 'description', 'long_description', 'quantity', 'unit', 'rate', 'tax_id', 'sub_total'
     	];

    protected $casts = [
        'tax_id' => 'array',
    ];

}
