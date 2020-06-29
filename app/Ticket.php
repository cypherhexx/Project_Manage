<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Comment;
use App\ArticleGroup;
use Notification;
use App\Notifications\NewSupportRequest;
use App\Notifications\NewCommentOnTicket;

class Ticket extends Model
{
    use \App\Traits\TagOperation;

    protected $fillable = [
       'number' ,'subject', 'project_id', 'customer_contact_id', 'name', 'email', 'department_id',
        'ticket_priority_id', 'ticket_service_id', 'created_by', 'assigned_to', 'ticket_status_id', 'user_type'
    ];


    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    function tasks()
    {
        return $this->hasMany(Task::class ,'component_number','id')->where('component_id', '=', COMPONENT_TYPE_TICKET);
            
    }

    function notes()
    {
        return $this->morphMany(Note::class, 'noteable')->orderBy('id', 'DESC');
    }

    function department()
    {
        return $this->belongsTo(Department::class);
    }

    function project()
    {
        return $this->belongsTo(Project::class);
    }

    function service()
    {
        return $this->belongsTo(TicketService::class, 'ticket_service_id', 'id');
    }

    function status()
    {
        return $this->belongsTo(TicketStatus::class, 'ticket_status_id', 'id');
    }

    function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'ticket_priority_id', 'id');
    }

    function assigned_user()
    {
        return $this->belongsTo(User::class ,'assigned_to','id')->withTrashed();
    }  


	static function sales_agent_dropdown()
    {
        

        return User::activeUsers()
                ->select(
                    DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->pluck('name', 'id')->toArray();
    }

    static function dropdown_for_filtering()
    {
       

        $data['department_id_list']         = Department::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        $data['ticket_status_id_list']      = TicketStatus::orderBy('sequence_number','ASC')->pluck('name', 'id')->toArray();
        $data['ticket_priority_id_list']    = TicketPriority::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        $data['ticket_service_id_list']     = TicketService::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        $data['customer_support_assistant_id_list']    = array('' => __('form.all')) + array('unassigned' => __('form.not_assigned')) +  self::sales_agent_dropdown();

        $data['default_ticket_status_ids'] = [ TICKET_STATUS_OPEN, TICKET_STATUS_IN_PROGRESS, TICKET_STATUS_ON_HOLD];

        return $data;
    }

    static function dropdown()
    {

        $select                         = __('form.dropdown_select_text');

        $data['customer_contact_id']        = [];       
        $data['project_id_list']            = [];
       
        $data['department_id_list'] = array('' => $select) + Department::orderBy('name','ASC')->pluck('name', 'id')->toArray();

        $data['ticket_status_id_list'] = TicketStatus::orderBy('sequence_number','ASC')->pluck('name', 'id')->toArray();
        $data['ticket_priority_id_list'] = array('' => $select) + TicketPriority::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        $data['ticket_service_id_list'] = array('' => $select) + TicketService::orderBy('name','ASC')->pluck('name', 'id')->toArray();

		$data['tag_id_list'] = Tag::orderBy('name','ASC')->pluck('name', 'id')->toArray();

		$data['pre_defined_replies_id'] = array('' => $select) + PreDefinedReply::orderBy('name','ASC')->pluck('name', 'id')->toArray();

		$data['customer_support_assistant_id_list']    =  self::sales_agent_dropdown();

        if(!is_knowledge_base_feature_disabled())
        {
            $data['knowledge_base_link_list']    =  self::knowledge_base_dropdown();    
        }
        



        return $data;
    }

    static function customer_dropdown()
    {

        $select                         = __('form.dropdown_select_text');       
        $data['department_id_list']         = array('' => $select) + Department::orderBy('name','ASC')
                ->whereNull('hide_from_client')->pluck('name', 'id')->toArray();

        
        $data['ticket_priority_id_list']    = array('' => $select) + TicketPriority::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        $data['ticket_service_id_list']     = array('' => $select) + TicketService::orderBy('name','ASC')->pluck('name', 'id')->toArray();

        $data['project_id_list']            = array('' => $select) + Project::orderBy('name','ASC')
        ->where('customer_id', auth()->user()->customer_id )->pluck('name', 'id')->toArray();

        return $data;
    }


    public static function statistics()
    {
        $stat = [
            'open'          => 0,
            'in_progress'   => 0,
            'answered'      => 0,
            'on_hold'       => 0,
            'closed'        => 0,
        ];

        $tickets = Ticket::select('ticket_status_id', DB::raw('count(*) as total'))
            ->groupBy('ticket_status_id');

        if(!check_perm('tickets_view'))
        {
            $tickets->where('assigned_to', auth()->user()->id);
        }

        $tickets = $tickets->pluck('total','ticket_status_id');


       
        if(count($tickets) > 0)
        {
            foreach ($tickets as $ticket_status_id=>$total) 
            {
                switch ($ticket_status_id) {
                    case TICKET_STATUS_OPEN:
                        $stat['open']    = $total;
                    break;
                    case TICKET_STATUS_IN_PROGRESS:
                        $stat['in_progress']           = $total;
                    break;    
                    case TICKET_STATUS_ANSWERED:
                        $stat['answered']       = $total;
                    break;
                    case TICKET_STATUS_ON_HOLD:
                        $stat['on_hold']        = $total;
                    break;
                    case TICKET_STATUS_CLOSED:
                        $stat['closed']         = $total;
                    break;       
                    default:
                    # code...
                    break;
                }
            }

           
        }

        

        return $stat;

    }

    public static function statistics_for_project($project_id)
    {
        $stat = [
            'open'          => 0,
            'in_progress'   => 0,
            'answered'      => 0,
            'on_hold'       => 0,
            'closed'        => 0,
        ];

        $tickets = Ticket::select('ticket_status_id', DB::raw('count(*) as total'))
            ->groupBy('ticket_status_id')->where('project_id', $project_id )
            ->pluck('total','ticket_status_id');      
       
        if(count($tickets) > 0)
        {
            foreach ($tickets as $ticket_status_id=>$total) 
            {
                switch ($ticket_status_id) {
                    case TICKET_STATUS_OPEN:
                        $stat['open']           = $total;
                    break;
                    case TICKET_STATUS_IN_PROGRESS:
                        $stat['in_progress']    = $total;
                    break;    
                    case TICKET_STATUS_ANSWERED:
                        $stat['answered']       = $total;
                    break;
                    case TICKET_STATUS_ON_HOLD:
                        $stat['on_hold']        = $total;
                    break;
                    case TICKET_STATUS_CLOSED:
                        $stat['closed']         = $total;
                    break;       
                    default:
                    # code...
                    break;
                }
            }

           
        }

        

        return $stat;

    }
    

    public function delete_has_many_relations($relations)
    {
        if(is_array($relations) && count($relations) > 0)
        {
            foreach($relations as $relation) 
            {
                $relation = $this->{$relation}()->get();

                if(count($relation) > 0)
                {
                    foreach ($relation as $r) 
                    {
                       $r->forcedelete();
                    }
                } 
            
                
            }
        }
    }

    function notify_new_ticket_created_by_customer()
    {
        $ticket = $this;
        
        $users = $ticket->department->team_members;

        if($users)
        {
            Notification::send($users, new NewSupportRequest($ticket));
        }
    }

    function notify_reply_from_customer(Comment $comment)
    {
        $ticket = $this;

        $page_number        = $ticket->comments()->paginate(TICKET_THREAD_PAGE_LENGTH)->lastPage();
        $link_to_comment    = route('show_ticket_page', $ticket->id). "?page=".$page_number."&jumpto=thread_".$comment->id; 

        $notifiable_users = [];

        // If the creator of the ticket is a team member add him as notifiable user
        if($ticket->user_type == USER_TYPE_TEAM_MEMBER)
        {
            array_push($notifiable_users, $ticket->created_by);
        }
       
        // Add the person who is assigned to the ticket
        if($ticket->assigned_to)
        {            
            array_push($notifiable_users, $ticket->assigned_to);
        }

        if(count($notifiable_users) > 0)
        {
            $users = User::whereIn('id', $notifiable_users)->get();
        }
        else
        {
            // Get all the team members of the department
            $users = $ticket->department->team_members()->get();
        }     
        
        
        //$users = $ticket->department->team_members;
        if(count($users) > 0)
        {
            Notification::send($users, new NewCommentOnTicket($ticket, $link_to_comment));    
        }
            
    }


    static function knowledge_base_dropdown()
    {
       
      $groups = ArticleGroup::accessible_by_cusotmer()->get();
      $data[0] = ['id' => '', 'text' => __('form.dropdown_select_text') , 'children' => '' ];

       if(count($groups) > 0)
       {
            $i = 1;
           foreach ($groups as $group) 
           {
                $children = NULL;

                $articles = $group->articles()->accessible_by_cusotmer()->get();

                if(count($articles) > 0)
                {
                    foreach ($articles as $article) 
                    {
                        $children[] = ['id' => route('knowledge_base_article_customer_view', $article->slug), 'text' => $article->subject];

                    }    
                }

                $data[$i] = ['id' => $group->id, 'text' => $group->name, 'children' => $children ];
                $i++;
           }
       }

       return $data;

    }


    function log_created()
    {
        $description = sprintf(__('form.act_created'), __('form.ticket'));
        $data_to_save = anchor_link($this->number, route('show_ticket_page', $this->id )) ;
        log_activity($this, trim($description), $data_to_save); 
    }

    function log_ticket_updated()
    {
        $description = sprintf(__('form.act_updated'), __('form.ticket'));
        $data_to_save = anchor_link($this->number, route('show_ticket_page', $this->id )) ;
        log_activity($this, trim($description), $data_to_save); 
    }



    function log_ticket_assigned($notifiable_member)
    {
        $link_to_the_ticket = anchor_link($this->number, route('show_ticket_page', $this->id ) );
        $name               = $notifiable_member->first_name . " ". $notifiable_member->last_name;

        $description        = sprintf(__('form.act_assigned'), __('form.ticket'). ' '.$link_to_the_ticket);
        $data_to_save       = anchor_link($name , route('member_profile', $notifiable_member->id ) );  

        log_activity($this, $description , $data_to_save);  
    }

    function log_ticket_unassigned($notifiable_member)
    {
        $link_to_the_ticket = anchor_link($this->number, route('show_ticket_page', $this->id ) );
        $name               = $notifiable_member->first_name . " ". $notifiable_member->last_name;

        $description        = sprintf(__('form.act_unassigned'), __('form.ticket'). ' '.$link_to_the_ticket);
        $data_to_save       = anchor_link($name , route('member_profile', $notifiable_member->id ) );  
          
        log_activity($this, $description , $data_to_save);  
    }

    function log_ticket_comment(Comment $comment)
    {
        $ticket = $this;

        $page_number        = $ticket->comments()->paginate(TICKET_THREAD_PAGE_LENGTH)->lastPage();
        $link_to_comment    = route('show_ticket_page', $ticket->id). "?page=".$page_number."&jumpto=thread_".$comment->id;

        $description = sprintf(__('form.act_commented_on'), __('form.ticket'));
        $data_to_save = anchor_link($this->number, $link_to_comment ) ;
        log_activity($this, trim($description), $data_to_save); 
    }
}
