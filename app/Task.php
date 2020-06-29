<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Follower;

class Task extends Model {

    use SoftDeletes;
    use \App\Traits\TagOperation;

    protected $dates = ['deleted_at'];

    

    protected $fillable = [
        'number' ,'start_date', 'due_date', 'is_billable', 'milestone_id', 'title', 'description',
        'hourly_rate', 'priority_id', 'component_id', 'component_number', 'parent_task_id', 
        'user_type','created_by', 'assigned_to', 'status_id'
    ];
    
    public function followers()
    {
        return $this->morphMany(Follower::class, 'followable');
    }


    public function get_notifiable_members($except = NULL)
    {
        $followers = $this->followers;
        
        if(count($followers) > 0)
        {
            $users = [];
            foreach ($followers as $follower) 
            {   
                if(count($except) > 0)
                {
                    if($except['user_type'] == $follower->user_type && $except['user_id'] == $follower->user_id)
                    {
                        // Blank
                        continue;
                    }
                    else
                    {
                        $users[] = $follower->user;        
                    }
                }
                else
                {
                    $users[] = $follower->user;
                }
                
            }
            return $users;
        }        
        return [];
        
    }

    public function add_follower($user_type, $user_id)
    {
        $does_follow = $this->followers->contains(function ($follower) use($user_type, $user_id) {
            return ( ($follower->user_type == $user_type) && ($follower->user_id == $user_id) );
        });

        if(!$does_follow)
        {
            $follower = new Follower();
            $follower->user_type    = $user_type;
            $follower->user_id      = $user_id;
            $this->followers()->save($follower);
        }

        
    }

    public function remove_follower($user_type, $user_id)
    {
        $this->followers()->where('user_type', $user_type)->where('user_id', $user_id)->delete();        
    }


    
    function priority()
    {
        return $this->belongsTo(Priority::class ,'priority_id','id');
    }

    function status()
    {
        return $this->belongsTo(TaskStatus::class ,'status_id','id');
    }

    function assigned_user()
    {
        return $this->belongsTo(User::class ,'assigned_to','id')->withTrashed();
    }

    static function dropdown_for_filtering()
    {
        $data['status_id_list']     = TaskStatus::orderBy('id','ASC')->pluck('name', 'id')->toArray();

        $data['priority_id_list']   = Priority::orderBy('id','ASC')->pluck('name', 'id')->toArray();

        $data['assigned_to_list']   = array('' => __('form.all')) + array('unassigned' => __('form.not_assigned')) +  User::activeUsers()->select(DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')
                                        ->pluck('name', 'id')->toArray();         

        $data['component_id_list']  = [
                'uncategorized'             => __('form.uncategorized'),
                COMPONENT_TYPE_CUSTOMER     => __('form.customer'),
                COMPONENT_TYPE_LEAD         => __('form.lead'),
                COMPONENT_TYPE_PROJECT      => __('form.project'),             
                COMPONENT_TYPE_PROPOSAL     => __('form.proposal'),
                COMPONENT_TYPE_TICKET       => __('form.ticket'),
        ];

        $data['sort_by'] = [
            ''             => __('form.n/a'),
            'due_date'             => __('form.due_date'),
            

        ];

        return $data;

    }

    static function dropdown($task_id = NULL)
    {

        $select = __('form.dropdown_select_text');
        $data['milestone_options'] = [];
        $data['component_number_options'] = [];
        $data['parent_task_id_list'] =  [];
        
        // $data['assigned_to_list'] = array('' => $select) + User::activeUsers()
        //         ->select(
        //             DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->pluck('name', 'id')->toArray();

        $data['status_id_list'] = TaskStatus::orderBy('id','ASC')->pluck('name', 'id')->toArray();

        $data['priority_id_list'] = Priority::orderBy('id','ASC')->pluck('name', 'id')->toArray();
        
        

        // $data['cron_run_types_id'] = [
        //         '' => $select,
        //         2 => 'Week',
        //         3 => '2 Weeks',
        //         4 => '1 Month',
        //         5 => '2 Months',
        //         6 => '3 Months',
        //         7 => '6 Months',
        //         8 => '1 Year',
        //         1 => 'Custom',
        //     ] ;

        $data['tag_id_list'] = Tag::orderBy('name','ASC')->pluck('name', 'id')->toArray();



        $data['assigned_to_list'] = array('' => $select) + User::activeUsers()
                ->select(
                    DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->pluck('name', 'id')->toArray();

        // $data['component_id_list'] = array('' => $select) + Component::orderBy('name','ASC')->pluck('name', 'id')->toArray();

        $data['component_id_list'] = [
                '' => $select,
                COMPONENT_TYPE_CUSTOMER => __('form.customer'),
                COMPONENT_TYPE_LEAD => __('form.lead'),
                COMPONENT_TYPE_PROJECT => __('form.project'),             
                COMPONENT_TYPE_PROPOSAL => __('form.proposal'),
                COMPONENT_TYPE_TICKET => __('form.ticket'),
        ];

        return $data;
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class,'component_number','id')->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class,'component_number','id')->select("id", 'name AS title')->withTrashed();
    }

    public function project()
    {
        return $this->belongsTo(Customer::class,'component_number','id');
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function timesheets()
    {
        return $this->hasMany(TimeSheet::class ,'task_id','id');
    }

    public function unbilled_timesheets()
    {
        return $this->hasMany(TimeSheet::class ,'task_id','id')->whereNull('invoice_id');
    }


    public function related_to()
    {
        if($this->component_id == COMPONENT_TYPE_LEAD)
        {
            return $this->belongsTo(Lead::class,'component_number','id')->select([DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id']);
        }
        elseif ($this->component_id == COMPONENT_TYPE_CUSTOMER)
        {
            return $this->belongsTo(Customer::class,'component_number','id');
        }
        elseif ($this->component_id == COMPONENT_TYPE_PROJECT)
        {
            return $this->belongsTo(Project::class,'component_number','id');
        }
        elseif ($this->component_id == COMPONENT_TYPE_PROPOSAL)
        {
            return $this->belongsTo(Proposal::class,'component_number','id');
        }
        elseif ($this->component_id == COMPONENT_TYPE_TICKET)
        {
            return $this->belongsTo(Ticket::class,'component_number','id')->select(['id', 'subject AS name']);
        }
        
    }

    public function component()
    {
        return $this->belongsTo(Component::class,'component_id','id');
    }


    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at', 'DESC');
    }

    

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')->orderBy('id', 'DESC');
    }


    public function sub_tasks()
    {
        return $this->hasMany(Task::class,'parent_task_id','id')->orderBy('id', 'ASC');
    }

    public function parent_task()
    {
        return $this->belongsTo(Task::class,'parent_task_id','id');
    }

    public function type()
    {
        return $this->belongsTo(Component::class,'component_id','id');
    }

    public function person_created()
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


    function time_sheet()
    {
        return $this->hasMany(TimeSheet::class);
    }

    function get_data_for_kanban_view($component_id = NULL, $component_number=NULL)
    {
        $list = [
            TASK_STATUS_BACKLOG             => [ 
                                                'name'      => __('form.backlog'), 
                                                'bg_color'  => '#B9D6F2', 'text_color' => '#fff' ,
                                                'tasks' => [] 
                                            ],
            TASK_STATUS_IN_PROGRESS         => [ 
                                                    'name' => __('form.in_progress') , 
                                                    'bg_color'  => '#593F62', 'text_color' => '#fff' ,
                                                    'tasks' => []
                                                ],
            TASK_STATUS_TESTING             => [ 
                                                    'name'      => __('form.testing') , 
                                                    'bg_color'  => '#FAA613', 'text_color' => '#fff' ,
                                                    'tasks'     => []
                                                ],
            TASK_STATUS_AWAITING_FEEDBACK   => [ 
                                                    'name' => __('form.awaiting_feedback') , 
                                                    'bg_color'  => '#688E26', 'text_color' => '#fff' ,
                                                    'tasks' => []
                                                ],
            TASK_STATUS_COMPLETE            => [ 
                                                    'name' => __('form.complete') , 
                                                    'bg_color'  => '#006DAA', 'text_color' => '#fff' ,
                                                    'tasks' => [] 
                                                ],          
        ];

        if($component_number && $component_id)
        {
            $tasks = Task::where('component_number', $component_number)->where('component_id', $component_id)->get();  
        }
        else
        {
            $tasks = Task::all();     
        }
        

        if(count($tasks) > 0)
        {
            foreach ($tasks as $task) 
            {
                $list[$task->status_id]['tasks'][] = $task ;
            }
        }

        return $list;

    }

    
    static function statistics()
    {
        $task =  Task::select('status_id', DB::raw('count(*) as total'))->groupBy('status_id');
            

        if(!check_perm('tasks_view'))
        {
            $task->where('assigned_to', auth()->user()->id);
        }
        
        $rec = $task->pluck('total','status_id');

        $statuses = [
            TASK_STATUS_BACKLOG,
            TASK_STATUS_IN_PROGRESS,
            TASK_STATUS_TESTING,
            TASK_STATUS_AWAITING_FEEDBACK,
            TASK_STATUS_COMPLETE
        ];
        foreach ($statuses as $status)
        {
            if(!isset($rec[$status]))
            {
                $rec[$status] = 0;
            }
        }

        return $rec;
    }


    static function home_page_stat()
    {
        $data['percent']    = 0;
        $data['figure']     = '0 / 0';

        $rec = Task::select('status_id', DB::raw('count(*) as total'))->groupBy('status_id')->pluck('total','status_id')->all();         

        if(count($rec) > 0)
        {
            $total_number                           = array_sum($rec);
            $number_of_completed_tasks              = (isset($rec[TASK_STATUS_COMPLETE])) ? $rec[TASK_STATUS_COMPLETE] : 0;
            $number_of_unfinished_tasks             = $total_number - $number_of_completed_tasks ;
            $data['figure']                         = $number_of_unfinished_tasks . " / ". $total_number;
            $data['percent']                        = round(($number_of_unfinished_tasks/$total_number) * 100);
           
            
        }
        return $data;
    }


    public function delete_has_many_relations($relations)
    {
        if(count($relations) > 0)
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



}