<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class NumberGenerator extends Model
{
    public $timestamps = false;

    static function gen($component_id)
    {
        $obj = self::where('component_id', $component_id)->get()->first();

        if($obj)
        {
            $obj->last_generated_value++;
            $generated_number = $obj->last_generated_value;

            $generated_number = sprintf('%06d', $generated_number);
        }
        else
        {
            $obj = new NumberGenerator();
            $obj->component_id = $component_id;
            $generated_number  = "000001";
        }

        $obj->last_generated_value = $generated_number;
        $obj->save();

        return Setting::get_prefix_by_component_id($component_id) ."-".$generated_number;
    }
}
