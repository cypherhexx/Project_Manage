<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Follower extends Model
{
	public $timestamps = false;
	
    public function followable()
    {
        return $this->morphTo();
    }


    public function user()
    {
    	if($this->user_type == USER_TYPE_TEAM_MEMBER)
    	{
    		return $this->belongsTo(User::class, 'user_id', 'id');
    	}
    	else
    	{
    		return $this->belongsTo(CustomerContact::class, 'user_id', 'id');	
    	}
    }
}
