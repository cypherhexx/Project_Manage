<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{


    use SoftDeletes;

    protected $dates = ['deleted_at'];


}
