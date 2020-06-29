<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use App\User;
use App\Attachment;
use App\Notifications\MemberMentionedInComment;

class Comment extends Model
{
    /**
     * Get all of the owning commentable models.
     */
    public function commentable()
    {
        return $this->morphTo();
    }



    public function user()
    {

        if($this->user_type == USER_TYPE_TEAM_MEMBER)
        {
            return $this->belongsTo(User::class,'user_id','id')->select(DB::raw("CONCAT(first_name,' ',last_name) AS name, photo"))->withTrashed();
                
                    
        }
        elseif ($this->user_type == USER_TYPE_CUSTOMER )
        {
            return $this->belongsTo(CustomerContact::class,'user_id','id')->select(DB::raw("CONCAT(first_name,' ',last_name) AS name, photo, customer_id"))->withTrashed();
        }
        elseif ($this->user_type == USER_TYPE_POTENTIAL_CUSTOMER )
        {
            return $this->belongsTo(PotentialCustomer::class,'user_id','id');
        }

    }


   function get_commenter_information()
   {
      if($this->user_type == USER_TYPE_CUSTOMER)
      {
          $data['type']  =   __('form.customer'); 
          $data['name']  =  anchor_link($this->user->name, route('show_customer_contact', $this->user_id ) );
      }
      else if($this->user_type == USER_TYPE_POTENTIAL_CUSTOMER)
      {
          $data['type']  =  __('form.potential_customer'); 
          $data['name']  =  $this->user->name; 
      }
      else
      {
          $data['type']  =   __('form.team_member'); 
          $data['name']  =  anchor_link($this->user->name, route('member_profile', $this->user_id) );
      }
      return $data;
   }

    
    function notify_members_of_mentions($message, $url_to_take_the_member)
    {
        preg_match_all(REGEX_PATTERN_TEAM_MEMBER_FROM_STRING, $message, $match_result);
        
        if(!empty($match_result))
        {

            $matches = $match_result[0];

            foreach ($matches as $key => $m) 
            {
               $member_short_code = strtr($m, REGEX_PATTERN_TEAM_MEMBER_FILTER_ARRAY );

               $user = User::where('short_code', $member_short_code)->get()->first();
               //
               $user->notify(new MemberMentionedInComment(auth()->user(), $this, $url_to_take_the_member ) );
               
            }
        }
    }

    function parsed_comment()
    {
        $text = $this->body;

        $text = preg_replace_callback(
                        REGEX_PATTERN_TEAM_MEMBER_FROM_STRING,
                function (array $m){

                    $member_short_code = strtr(trim($m[0]), REGEX_PATTERN_TEAM_MEMBER_FILTER_ARRAY );                  

                    $user = User::where('short_code', $member_short_code)->get();

                    if(count($user) > 0)
                    {
                        $user = $user->first();
                        return anchor_link($user->first_name . " " . $user->last_name, route('member_profile', $user->id) );    
                    }
                    
                    
                   
                },
                $text
            );


        $text = preg_replace_callback(
                        REGEX_PATTERN_ATTACHMENT_FROM_STRING,
                function (array $m){

                    $short_code = strtr(trim($m[0]), REGEX_PATTERN_ATTACHMENT_FILTER_ARRAY );                  

                    $attachment = Attachment::where('short_code', $short_code)->get();

                    if(count($attachment) > 0)
                    {
                        $attachment = $attachment->first();
                        
                        $download_url = route('attachment_download_link', Crypt::encryptString($attachment->name) );

                        $extension = pathinfo($attachment->name, PATHINFO_EXTENSION);

                        if(in_array($extension, ['jpg', 'jpeg', 'bmp', 'png', 'psd'] ))
                        {
                            $file_source = Storage::url($attachment->name) ;
                        }
                        else
                        {
                            $file_source = asset('images/attachment.png');
                            
                        }

                        return '<br><br><a target="_blank" href="'.$download_url.'"><i class="fas fa-paperclip"></i> '. $attachment->display_name.'</a><br>';
                        
                    }
                    
                    
                   
                },
                $text
            );

        return $text;
    }


    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')->orderBy('id', 'DESC');
    }
}
