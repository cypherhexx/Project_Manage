<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class ProposalItem extends Model {

    public $timestamps = false;

    protected $fillable = [
            'proposal_id', 'description', 'long_description', 'quantity', 'unit', 'rate', 'tax_id', 'sub_total'
    ];


    protected $casts = [
        'tax_id' => 'array',
    ];

}