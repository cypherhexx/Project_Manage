<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Reminder extends Model
{
	protected $fillable = [
       'remindable_id' ,'remindable_type', 'send_reminder_to', 'date_to_be_notified', 'description', 'is_notified', 'created_by',
        
    ];

    /**
     * Get all of the owning commentable models.
     */
    public function remindable()
    {
        return $this->morphTo();
    }

    static function dropdown()
    {
    	$select = __('form.dropdown_select_text');

    	$data['remind_to_list']   = array('' => $select ) +  User::names_for_dropdown();     

    	return $data;
    }

    function remind_to()
    {
    	return $this->belongsTo(User::class, 'send_reminder_to', 'id')->name();
    }

    
}
