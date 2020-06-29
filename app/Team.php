<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Team extends Model
{
    public $timestamps = false;

    function leader()
    {
        return $this->belongsTo(User::class ,'leader_user_id','id');
    }

    function members()
    {
        return $this->belongsToMany(User::class, 'user_teams', 'team_id', 'user_id');
    }

    static function dropdown()
    {

        $select = __('form.dropdown_select_text');

        $data['users_list'] = User::activeUsers()
                ->select(
                    DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->pluck('name', 'id')->toArray();


        return $data;
    }

}
