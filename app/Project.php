<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\TaskStatus;
use Carbon\Carbon;

class Project extends Model {

    use SoftDeletes;
    use \App\Traits\TagOperation;

    protected $dates = ['deleted_at'];

    function customer()
    {
        return $this->belongsTo(Customer::class ,'customer_id','id')->withTrashed();
    }


    function billing_type()
    {
        return $this->belongsTo(BillingType::class ,'billing_type_id','id');
    }


    function status()
    {
        return $this->belongsTo(ProjectStatus::class ,'status_id','id');
    }

    function tickets()
    {
        return $this->hasMany(Ticket::class);
            
    }

    function tasks()
    {
        return $this->hasMany(Task::class ,'component_number','id')
            ->where('component_id', '=', COMPONENT_TYPE_PROJECT);
    }

    function open_tasks()
    {
        return $this->hasMany(Task::class ,'component_number','id')
            ->where('component_id', '=', COMPONENT_TYPE_PROJECT)
            ->where('status_id', '<>', TASK_STATUS_COMPLETE);
    }

    function open_tasks_as_dropdown()
    {
        return array('' => __('form.dropdown_select_text') ) + $this->open_tasks->pluck('title', 'id')->toArray();
    }

    function get_task_summary()
    {
        $rec = $this->hasMany(Task::class ,'component_number','id')

            ->where('component_id', '=', COMPONENT_TYPE_PROJECT)
            ->select('status_id', DB::raw('count(*) as total'))
            ->groupBy('status_id')
            ->pluck('total','status_id')->all();

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


    function members()
    {
        return $this->belongsToMany(User::class ,'project_members','project_id','user_id');
    }


    function members_as_dropdown()
    {
        return $this->members()->select(
            DB::raw("CONCAT(first_name,' ',last_name) AS name"),'users.id')->pluck('name', 'users.id')
         ->toArray();
    }


    
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')->orderBy('id', 'DESC');
    }

    function milestones()
    {
        return $this->hasMany(Milestone::class ,'project_id','id')->orderBy('order', 'ASC');

    }

    function timesheets()
    {
        return $this->hasManyThrough(TimeSheet::class, Task::class,
            'component_number', 
            'task_id',
            'id', 
            'id' 
        )->where('component_id', COMPONENT_TYPE_PROJECT);
    }


    function expenses()
    {
        return $this->hasMany(Expense::class ,'project_id','id')->orderBy('date', 'ASC');

    }

    function unbilled_expenses()
    {
        return $this->expenses()->whereNull('invoice_id')->where('is_billable', TRUE);

    }




   
    static function dropdownForFiltering()
    {  

        $data['status_id_list'] = ProjectStatus::orderBy('id', 'ASC')->pluck('name', 'id')->toArray();        

        return $data;
    }

    static function dropdown()
    {
        $select = __('form.dropdown_select_text');

        $data['customer_id_list'] = array('' => $select) + Customer::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();

        $data['billing_type_id_list'] = array('' => $select) + BillingType::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();

        $data['status_list'] = ProjectStatus::orderBy('id', 'ASC')->pluck('name', 'id')->toArray();

        $data['user_id_list'] = User::activeUsers()
                ->select(
                    DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->pluck('name', 'id')->toArray();

        $data['tag_id_list'] = Tag::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();

        

        return $data;
    }

    public static function statistics($customer_id = NULL)
    {
        $stat = [
            'not_started' => 0,
            'in_progress' => 0,
            'on_hold' => 0,
            'cancelled' => 0,
            'finished' => 0,
        ];

        $query = Project::select(['status_id', DB::raw('count(*) as total')])->groupBy('status_id');            
            
        if($customer_id)
        {
          $query->where('customer_id', $customer_id);  
        }

        if(!check_perm('projects_view'))
        {
           // Get the Project Ids that the current user is involved in
            $fetched_project_ids        = self::get_project_ids_that_the_current_user_is_involved_in();           
      
            $query->whereIn('id', $fetched_project_ids);
        }
        
        $projects = $query->get();

        if(count($projects) > 0)    
        {
            foreach($projects as $project)
            {
    
                switch ($project->status_id) 
                {
                        case PROJECT_STATUS_NOT_STARTED:
                        $stat['not_started'] = $project->total ;
                        break;
                        case PROJECT_STATUS_IN_PROGRESS:
                        $stat['in_progress'] = $project->total ;
                        break;
                        case PROJECT_STATUS_ON_HOLD:
                        $stat['on_hold'] = $project->total ;
                        break;
                        case PROJECT_STATUS_CANCELLED:
                        $stat['cancelled'] = $project->total ;
                        case PROJECT_STATUS_FINISHED:
                        $stat['finished'] = $project->total ;
                        break;
                    
                    
                }
         
            }
        }      

        return $stat;

    }


    function timesheet_stat()
    {

        $rec = $this;

        $timesheets = $rec->timesheets()->get();  
        

        $logged_hours               = [];
        $billable_hours             = [];
        $billed_hours               = [];
        $unbilled_hours             = [];
        $logged_hours_amount        = [];
        $billable_hours_amount      = [];
        $billed_hours_amount        = [];
        $unbilled_hours_amount      = [];


        $t = [
                'logged_hours'              => "00:00",
                'billable_hours'            => "00:00",
                'billed_hours'              => "00:00",
                'unbilled_hours'            => "00:00",

                'logged_hours_amount'       => 0,
                'billable_hours_amount'     => 0,
                'billed_hours_amount'       => 0,
                'unbilled_hours_amount'     => 0,
            ];

        if(count($timesheets) > 0) 
        {
            foreach ($timesheets as $key => $row) 
            {
                $rate =   ($rec->billing_type_id == BILLING_TYPE_PROJECT_HOURS) ? $rec->billing_rate : $row->task->hourly_rate;

                if($row->duration)
                {
                    // $start_time = Carbon::createFromFormat('Y-m-d H:i:s' , $row->start_time);
                    // $end_time   = Carbon::createFromFormat('Y-m-d H:i:s' , $row->end_time);
                    $logged_hours[]                     = $row->duration;
                    $logged_hours_amount[]              = $this->calculate_task_cost($row->duration, $rate) ;

                    if($rec->billing_type_id            != BILLING_TYPE_FIXED_RATE)
                    {
                        $billable_hours[]               = $row->duration;
                        $billable_hours_amount[]        = $this->calculate_task_cost($row->duration, $rate) ;
                        // If Billed get the billed hours
                        if($row->invoice_id)
                        {
                            $billed_hours[]             = $row->duration;
                            $billed_hours_amount[]      = $this->calculate_task_cost($row->duration, $rate) ;
                        }
                        else
                        {   
                            // get the unbilled hours
                            $unbilled_hours[]           = $row->duration;
                            $unbilled_hours_amount[]    = $this->calculate_task_cost($row->duration, $rate) ;
                        }
                    }
                }
                    
                    
            }

            $t = [
                'logged_hours'      => $this->sum_time($logged_hours),
                'billable_hours'    => $this->sum_time($billable_hours),
                'billed_hours'      => $this->sum_time($billed_hours),
                'unbilled_hours'    => $this->sum_time($unbilled_hours),

                'logged_hours_amount'       => format_currency(array_sum($logged_hours_amount), TRUE ),
                'billable_hours_amount'     => format_currency(array_sum($billable_hours_amount),TRUE ),
                'billed_hours_amount'       => format_currency(array_sum($billed_hours_amount), TRUE ),
                'unbilled_hours_amount'     => format_currency(array_sum($unbilled_hours_amount), TRUE ),
            ];
           
        }

        return $t;
    }

    // The following function is also being used in InvoiceController
    public function calculate_task_cost($duration, $hourly_rate)
    {
        $duration = time_to_decimal($duration);

        if(is_numeric($duration) && is_numeric($hourly_rate))
        {
            return round($duration * $hourly_rate) ;
        }
        else
        {
            return 0;
        }
    }
    
    // The following function is also being used in InvoiceController
    public function sum_time($times) 
    {
        if($times)
        {
            $minutes = 0; //declare minutes either it gives Notice: Undefined variable
            // loop throught all the times
            
            foreach ($times as $time) 
            {
                list($hour, $minute) = explode(':', $time);
                $minutes += $hour * 60;
                $minutes += $minute;
            }

            $hours = floor($minutes / 60);
            $minutes -= $hours * 60;

            // returns the time already formatted
            return sprintf('%02d:%02d', $hours, $minutes);
        }
    }


    function expenses_stat()
    {
        $rec = $this;

        $expenses = $rec->expenses()->get();

        $total_expenses     = 0;
        $billable_expenses  = 0;
        $billed_expenses    = 0;
        $unbilled_expenses  = 0;


        $t = [
                'total_expenses'        => 0,
                'billable_expenses'     => 0,
                'billed_expenses'       => 0,
                'unbilled_expenses'     => 0,
            ];

        if(count($expenses) > 0)    
        {
            foreach ($expenses as $row) 
            {
               $t['total_expenses'] += $row->amount_after_tax;

               if($row->is_billable)
               {
                    $t['billable_expenses'] += $row->amount_after_tax;
               }

               if($row->invoice_id)
               {
                    $t['billed_expenses'] += $row->amount_after_tax;
               }

               if($row->is_billable && !$row->invoice_id)
               {
                    $t['unbilled_expenses'] += $row->amount_after_tax;
               }
            }
        }

        
        return $t;
    }

    function progress_percentage()
    {
        $percentCompleted = 0;

        if($this->calculate_progress_through_tasks)
        {
            $tasks = $this->tasks()->get();

            if(count($tasks) > 0)
            {
                $complete       = [];
                $in_complete    = [];

                foreach ($tasks as $task) 
                {
                    if($task->status_id == TASK_STATUS_COMPLETE)
                    {
                        $complete[] = 1;
                    }
                    else
                    {
                        $in_complete[] = 1;
                    }
                }

                $complete_count     = count($complete);
                $in_complete_count  = count($in_complete);
                $number_of_tasks    = $complete_count + $in_complete_count;

                $percentCompleted = ($complete_count / $number_of_tasks) * 100 ;

               
            }            
        }
        else
        {

            $percentCompleted = $this->progress;
        }

        return round($percentCompleted, 1);
    }

    private function prepare_array_for_gantt($tasks, $label_name)
    {
        $i = 0;
        $d = [];        
        if($tasks)
        {
            $j = 0;
            foreach ($tasks as $key=>$task) 
            {
                $custom_class       = '';

                
                if(true)
                {
                    // If task is complete display it in green label
                    if($task->status_id == TASK_STATUS_COMPLETE)
                    {
                       $custom_class =  'ganttGreen';
                    }

                    $carbon_due_date = Carbon::parse($task->due_date);
                    
                    // If the due date of the task has been passed and still it is not finished display it in red label
                    if($carbon_due_date->isPast() && ($task->status_id != TASK_STATUS_COMPLETE) )
                    {
                        $custom_class =  'ganttRed';
                    }
      


                    $d[$i]['name']      = ($j == 0) ? $label_name : " ";
                    $d[$i]['desc']      = $task->title;
                    $d[$i]['values'][]  = [
                                            'from'      =>  ($task->start_date) ? date("Y/m/d", strtotime($task->start_date)) : "",
                                            'to'        =>  ($task->due_date) ? date("Y/m/d", strtotime($task->due_date)) : "",
                                            // 'from'      => "2018/09/06",
                                            // 'to'      => "2018/09/27",
                                            'label'     => $task->title,
                                            'desc'      => $task->title,
                                            'dataObj'   => ['task_id' => $task->id],
                                            'customClass' => $custom_class,
                                            

                    ];
                   $i++;
                   $j++;
                }
                
            }
        }
        else
        {
            $d[$i]['name'] = $label_name ;
            $d[$i]['desc'] = "";
            $d[$i]['values'] = [];
            $i++;
        }

        return $d;
    }
   
    function gantt_chart_data_milestone()
    {
      
        $project    = $this;
        $d          = [];

      
        if(isset($project->milestones) && count($project->milestones) > 0)
        {
            $i = 0;
            
            foreach ($project->milestones as $milestone) 
            {
     
               $d = array_merge($d, $this->prepare_array_for_gantt($milestone->tasks, $milestone->name ));                
            }
        }
        else
        {
            $d = [];
        }

       
        return json_encode($d);        
    }

    

    function gantt_chart_data_project_status()
    {
        $d= [];

        $task_statuses = TaskStatus::all()->pluck('name', 'id')->toArray();        

        $project_tasks = $this->tasks()->get();

        if(count($project_tasks) > 0)
        {        
            $tasks_indexed_by_status_id = [];            

            foreach ($project_tasks as $row) 
            {
                $tasks_indexed_by_status_id[$row->status_id][] =  $row;
            }        
         

            foreach ($task_statuses as $k=>$task_status) 
            {
                $tasks_by_status = (isset($tasks_indexed_by_status_id[$k])) ? $tasks_indexed_by_status_id[$k] : [];

                $d = array_merge($d, $this->prepare_array_for_gantt($tasks_by_status, $task_status ));   

            }        
        }
        else
        {
             $d = [];
        }
            
        
        
       
      return json_encode($d);        
        
    }


    function gantt_chart_data_project_members()
    {

        $d = [];    
        $members = $this->members()->get();

        $project_tasks = $this->tasks()->whereNotNull('assigned_to')->get();


        if(count($project_tasks) > 0)
        {           
            $tasks_indexed_by_member_id = [];            

            foreach ($project_tasks as $row) 
            {
                $tasks_indexed_by_member_id[$row->assigned_to][] =  $row;
            }        
         

            foreach ($members as $k=>$member) 
            {
                $tasks_by_members = (isset($tasks_indexed_by_member_id[$member->id])) ? $tasks_indexed_by_member_id[$member->id] : [];


                $d = array_merge($d, $this->prepare_array_for_gantt($tasks_by_members, $member->first_name . " ". $member->last_name ));
                
            }
    
        }
        else
        {
             $d = [];
        }
            
       return json_encode($d);  
      
        
    }
    // The following is beinged used in this class, ProjectController and Helpers.php
    static function get_project_ids_that_the_current_user_is_involved_in()
    {

        // Get the Project Ids that the current user is involved in
        $sql = "SELECT id FROM (
                        SELECT project_id AS id FROM project_members WHERE user_id = ?
                        UNION ALL
                        SELECT id FROM projects WHERE created_by = ?
                    ) a GROUP BY id ORDER BY id";
        
        $fetched_project_ids = DB::select($sql, [auth()->user()->id, auth()->user()->id ]);

        if(count($fetched_project_ids) > 0)
        {
            $fetched_project_ids = array_map(function($value) {
                return (array)$value;
            }, $fetched_project_ids);
        }
        else
        {
            $fetched_project_ids = [];
        }

        return $fetched_project_ids;
    }

    static function home_page_stat()
    {
        $data['percent']    = 0;
        $data['figure']     = '0 / 0';

        $rec = Project::select('status_id', DB::raw('count(*) as total'))->groupBy('status_id')->pluck('total','status_id')->all(); 
        
       
        

        if(count($rec) > 0)
        {
            $total_number                           = array_sum($rec);
            $number_of_projects_in_progress         = (isset($rec[PROJECT_STATUS_IN_PROGRESS])) ? $rec[PROJECT_STATUS_IN_PROGRESS] : 0;
            $data['figure']                         = $number_of_projects_in_progress . " / ". $total_number;
            $data['percent']                        = round(($number_of_projects_in_progress/$total_number) * 100);
           
            
        }
        return $data;
    }
}