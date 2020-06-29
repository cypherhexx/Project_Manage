<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;


class Item extends Model
{


    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'item_category_id')->withTrashed();
    }


    public function tax_1()
    {
        return $this->belongsTo(Tax::class, 'tax_id_1')->withTrashed();
    }

    public function tax_2()
    {
        return $this->belongsTo(Tax::class, 'tax_id_2')->withTrashed();
    }




    public static function drop_downs()
    {
        $select = __('form.dropdown_select_text');
        $data['item_category_list'] = array('' => $select) + ItemCategory::pluck('name', 'id')->toArray();
        $data['taxes_list'] = array('' => $select) +  Tax::select(DB::Raw('CONCAT(concat_ws("@",name, rate), "%") as name'), 'id')->pluck('name', 'id')->toArray();

        return $data;
    }


}
