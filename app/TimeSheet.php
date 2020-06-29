<?php

namespace App;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TimeSheet extends Model
{
    //

    function task()
    {
        return $this->belongsTo(Task::class ,'task_id','id');
    }

    function member()
    {
        return $this->belongsTo(User::class ,'user_id','id');
    }


    static function dropdown_for_filtering()
    {


        $data['team_member_id_list']   = array('' => __('form.all')) +  User::activeUsers()->select(DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->pluck('name', 'id')->toArray();                                                

   
        return $data;

    }
}
