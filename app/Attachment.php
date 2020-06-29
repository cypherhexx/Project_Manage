<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Attachment extends Model
{
    
    public function attachable()
    {
        return $this->morphTo();
    }


    function add($files, $componentInstance)
    {
    	$files = (is_array($files)) ? $files : [$files];
        
        if(count($files) > 0)
        {
            foreach($files as $file)
            {
                $file_information           = decrypt($file);
                $attachment                 = new Attachment();
                $attachment->short_code     = strtr($file_information['short_code'] , REGEX_PATTERN_ATTACHMENT_FILTER_ARRAY );   
                $attachment->name           = $file_information['name'];
                $attachment->display_name   = $file_information['display_name'];
                $attachment->user_type      = (isset(auth()->user()->customer_id)) ? USER_TYPE_CUSTOMER : USER_TYPE_TEAM_MEMBER ;
                $attachment->created_by     = auth()->user()->id ;
                $componentInstance->attachments()->save($attachment);
            }
        }
    }

    function add_single_file_with_display_name($file, $componentInstance, $display_name)
    {
        
        if($file)
        {           
            $file_information           = decrypt($file);
            $attachment                 = new Attachment();
            $attachment->short_code     = strtr($file_information['short_code'] , REGEX_PATTERN_ATTACHMENT_FILTER_ARRAY );   
            $attachment->name           = $file_information['name'];
            $attachment->display_name   = $display_name;
            $attachment->user_type      = (isset(auth()->user()->customer_id)) ? USER_TYPE_CUSTOMER : USER_TYPE_TEAM_MEMBER ;
            $attachment->created_by     = auth()->user()->id ;
            $componentInstance->attachments()->save($attachment);
        }
    }

    function add_from_console($files, $componentInstance, $user_type, $user_id)
    {
        $files = (is_array($files)) ? $files : [$files];
        
        if(count($files) > 0)
        {
            foreach($files as $file)
            {
               
                $attachment                 = new Attachment();
                $attachment->short_code     = $file['short_code'];   
                $attachment->name           = $file['name'];
                $attachment->display_name   = $file['display_name'];
                $attachment->user_type      = $user_type ;
                $attachment->created_by     = $user_id;
                $componentInstance->attachments()->save($attachment);
            }
        }
    }


    function person_created()
    {
        if($this->user_type == USER_TYPE_CUSTOMER)
        {
            return $this->belongsTo(CustomerContact::class,'created_by','id')
            ->select(DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->withTrashed();
        }
        else
        {
            return $this->belongsTo(User::class,'created_by','id')
            ->select(DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->withTrashed();
        }
    }
}
