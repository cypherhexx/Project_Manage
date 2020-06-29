<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Tax;
use App\Lead;
use App\Task;
use App\Comment;
use App\Project;
use App\Proposal;
use App\Customer;
use App\Attachment;
use App\TaskStatus;
use App\User;
use App\Ticket;
use App\NumberGenerator;
use App\Follower;
use App\ProjectMember;

use App\Notifications\MemberMentionedInTaskComment;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskStatusChanged;

use App\Notifications\CommentOnTask;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data                       = Task::dropdown_for_filtering();
        $data['default_status_ids'] = [ TASK_STATUS_BACKLOG, TASK_STATUS_IN_PROGRESS , TASK_STATUS_TESTING, TASK_STATUS_AWAITING_FEEDBACK ];

        $data['task_summary']       = Task::statistics();


        return view('task.index', compact('data'));
    }

    function kanban_view()
    {
        
        $task   = new Task(); 
        $rec    = $task->get_data_for_kanban_view();
        $data   = [];
        return view('task.kanban_view', compact('data'))->with('rec', $rec ); 
    }

    function paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $q                  = Task::query();
        $query              = Task::with(['status', 'assigned_user', 'tags', 'priority', 'type']);
                                

        $component_id       = Input::get('component_id');
        $component_number   = Input::get('component_number');
        $status_id          = Input::get('status_id');
        $priority_id        = Input::get('priority_id');
        $assigned_to        = Input::get('assigned_to');
        $sort_by            = Input::get('sort_by');
       
        /* If the current user doesn't have permission to view all tasks, then 
            show only the ones that he is assigned to or has created.
        */
        if(!check_perm('tasks_view') && (!($component_id && $component_number)) )
        {    
            $q->where(function($k){
                $k->where('created_by', auth()->user()->id)->orWhere('assigned_to', auth()->user()->id);
            });              

            $query->where(function($k){
                $k->where('created_by', auth()->user()->id)->orWhere('assigned_to', auth()->user()->id);
            });  
        }
               

        if($component_id && $component_number)
        {
            $q->where('component_id', '=', $component_id)->where('component_number', '=', $component_number); 
                
            $query->where('component_id', '=', $component_id)->where('component_number', '=', $component_number);
                
        }
        elseif($component_id && !$component_number)
        {
            if($component_id == 'uncategorized')
            {
                $query->whereNull('component_id');
            }
            else
            {
                $query->whereIn('component_id',  $component_id);    
            }            
                
        }


        if($status_id)
        {
           $query->whereIn('status_id', (is_array($status_id)) ? $status_id : [$status_id] ); 
        }

        if($priority_id)
        {
            $query->whereIn('priority_id', $priority_id ); 
        }

        if($assigned_to)
        {
            if($assigned_to == 'unassigned')
            {
                $query->whereNull('assigned_to');
            }
            else
            {
                $query->where('assigned_to', '=', $assigned_to);   
            }
        }

        if($sort_by)
        {
            if($sort_by == 'due_date')
            {
                $query->orderBy('due_date', 'ASC');
            }
        }
        else
        {
            $query->orderBy('id', 'DESC');
        }

        $number_of_records = $q->count();

        if($search_key)
        {
            $query->where('title', 'like', $search_key.'%')
                // ->orWhere('start_date', '=', date2sql($search_key))
                // ->orWhere('due_date', '=', date2sql($search_key))
                // ->orWhereHas('status', function ($q) use ($search_key) {
                //     $q->where('project_statuses.name', 'like', $search_key.'%');
                    
                // })
            ;


        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();
//

        $rec = [];

        if (count($data) > 0)
        {
            $status_id_list = TaskStatus::orderBy('id','ASC')->pluck('name', 'id')->toArray();

            foreach ($data as $key => $row)
            {

              $person_created_route_name = ($row->user_type == USER_TYPE_CUSTOMER) ? 'view_customer_page' : 'member_profile' ;
               
               if(isset($row->assigned_user->first_name))
               {
                    $assigned_to = anchor_link($row->assigned_user->first_name . " ". $row->assigned_user->last_name, route('member_profile', $row->assigned_user->id)) ;
               }
               else
               {
                 $assigned_to = "";
               }

                if(check_perm('tasks_edit'))
                {
                    $status = $this->status_change_dropdown($row->id, $row->status->id, $row->status->name, $status_id_list);
                }
                else
                {
                    $status = $row->status->name;
                }

                $rec[] = array(

                    a_links(anchor_link($row->number, route('show_task_page', $row->id)), [
                        [
                            'action_link' => route('edit_task_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission'  => 'tasks_edit',
                        ],
                        [
                            'action_link' => route('delete_task', $row->id), 
                            'action_text' => __('form.delete'), 'action_class' => 'delete_item',
                            'permission'  => 'tasks_delete',
                        ]
                    ]),
                    $row->title,                   
                    $status,
                    ($row->start_date) ? sql2date($row->start_date) : "",
                    ($row->due_date) ? sql2date($row->due_date) : "",
                    $assigned_to,
                    $row->get_tags_as_badges(true),
                    $row->priority->name,
                    anchor_link($row->person_created->name , route($person_created_route_name, $row->person_created->id))
                    
                );

            }
        }


        $output = array(
            "draw" => intval(Input::get('draw')),
            "recordsTotal" => $number_of_records,
            "recordsFiltered" => $recordsFiltered,
            "data" => $rec
        );


        return response()->json($output);


    }

    private function status_change_dropdown($task_id, $status_id, $status_name, $status_id_list)
    {
        ob_start();
        ?>

        <div class="dropdown" >
                <button class="btn btn-sm btn-link dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                   <?php echo $status_name; ?>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                   <?php if(is_countable($status_id_list) && count($status_id_list) > 0)
                   {
                        foreach($status_id_list as $id=>$name)
                        {
                            if($id != $status_id)
                            {
                                ?>
                                <a class="dropdown-item change_task_status" data-task="<?php echo $task_id; ?>" data-name="<?php echo $name; ?>" data-id="<?php echo $id; ?>" href="<?php echo route('task_change_status_ajax'); ?>"><?php echo $name; ?></a>
                            <?php    
                            }
                        }    
                        
                    }
                    ?>
                </div>
            </div>

        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = Task::dropdown();
        return view('task.create', compact('data'))->with('rec','');
    }

    
    function convert_ticket_to_task($ticket_comment_thread_id)
    {
        $comment    = Comment::find($ticket_comment_thread_id);

        $ticket     = $comment->commentable;


        $rec                = new \stdClass();
        $rec->component_id  = COMPONENT_TYPE_TICKET;
        $rec->title         = $ticket->subject;
        $rec->description   = $comment->body;

        $data = Task::dropdown();
        $data['component_number_options'] = [ $ticket->id => $ticket->subject ];
        return view('task.create', compact('data'))->with('rec', $rec);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [           
            'title' => 'required|max:190'
        ];


        if(isset($request->component_id) && $request->component_id)
        {
            $rules['component_number'] = 'required';
        }

        if(isset($request->is_billable) && $request->is_billable)
        {
            $rules['hourly_rate'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules, [
            'component_number.required' => __('form.this_field_is_required'),
        ]);

        if ($validator->fails())
        {
            if($request->ajax())
            {
                return response()->json(['status' => 2 ,'errors'=> $validator->errors()]);
            }
            else
            {
                return  redirect()->back()
                ->withErrors($validator)
                ->withInput();
            }

            
        }


        DB::beginTransaction();
        $success = false;

        try {

            $request['number']          = NumberGenerator::gen(COMPONENT_TYPE_TASK);
            $request['start_date']      = date2sql($request->start_date) ;
            $request['due_date']        = date2sql($request->due_date)  ;
            $request['created_by']      = auth()->user()->id;
            $request['user_type']       = USER_TYPE_TEAM_MEMBER;            

            
            // Saving Data        
            $obj  = Task::create($request->all());   


            $obj->tag_attach($request->tag_id);

            // Save the attachments (If exists)
            $files = $request->attachment;
       
            if(is_array($files) && count($files) > 0)
            {
                $attachment = new Attachment();
                $attachment->add($files, $obj);       
            
            }

            // Follow the task for any changes 
            $obj->add_follower(USER_TYPE_TEAM_MEMBER, auth()->user()->id);
            
            // If the person assigned to the task is not the person who created it then add him as a follower of the task

            if($obj->assigned_to && ($obj->created_by != $obj->assigned_to))
            {         
                $obj->add_follower(USER_TYPE_TEAM_MEMBER, $obj->assigned_to);
            }


            // Log Activity 
            
            $log_name       = activity_log_name_by_componet_id($obj->component_id). $request->component_number;     
            
            $description    = sprintf(__('form.act_created'), __('form.task'));       
            
            log_activity($obj, trim($description), anchor_link($obj->title, route('show_task_page', $obj->id ) ) , $log_name );  

            // If the person assigned to the task is not the person who created it then send a notification
            if($request->assigned_to && $request->assigned_to != auth()->user()->id)
            {
                $member = User::find($request->assigned_to); 
                
                if($member)                
                {
                    $member->notify(new TaskAssigned($obj, auth()->user()));    
                }
                
            }               
            

            DB::commit();
            $success = true;


        } catch (\Exception  $e) {

            $success = false;
            DB::rollback();
            
        }

        if($request->ajax())
        {
            if ($success) 
            {
                return response()->json(['status' => 1 ,'msg'=>  __('form.success_add') ]);
            }
            else 
            {
                return response()->json(['status' => 3 ,'msg'=>  __('form.could_not_perform_the_requested_action') ]);
            }
        }
        else
        {
            if ($success) 
            {
                // the transaction worked ...
                session()->flash('message', __('form.success_add'));
                return redirect()->route('task_list');
            } 
            else 
            {
                session()->flash('message', __('form.could_not_perform_the_requested_action'));
                return redirect()->route('task_list');
            }
        }

        


   


    }

    private function is_task_belongs_to_user_project(Task $task)
    {
       $count = ProjectMember::where('project_id', $task->component_number)
            ->where('user_id', auth()->user()->id )->get()->count(); 
 
        return ($count > 0) ? TRUE : FALSE;
          
    }

    private function handle_no_permission_to_view_task(Task $task)
    {
        if(!(($task->created_by == auth()->id()) || $task->assigned_to == auth()->id() ||
                ($task->component_number && $task->component_id == COMPONENT_TYPE_PROJECT 
                   && ($this->is_task_belongs_to_user_project($task))
                )
            ))
        {
            abort(404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        /* If the current user has no permission to view tasks, then check this task was created or assigned to this
            current user
        */

        if(!check_perm('tasks_view'))
        {      
            $this->handle_no_permission_to_view_task($task);
        }

        $select = __('form.dropdown_select_text');
        $data['status_id_list'] = TaskStatus::orderBy('id','ASC')->get();
        $data['assigned_to_list'] = array('' => $select) + User::activeUsers()
                ->select(
                    DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->pluck('name', 'id')->toArray();

        return view('task.show', compact('data'))->with('rec',$task);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        $data = Task::dropdown();
        //$task->tag_id = $task->tags();
        $task->tag_id = $task->tags()->pluck('tag_id')->toArray();

        if($task->component_id)
        {
            $data['component_number_options'] = (isset($task->related_to->name)) ? [ $task->related_to->id => $task->related_to->name] : [];
        }
        if($task->milestone_id)
        {
            $data['milestone_options'] = (isset($task->milestone->name)) ? [ $task->milestone->id => $task->milestone->name] : [];
        }
        if($task->parent_task_id)
        {
            $data['parent_task_id_list'] = (isset($task->parent_task->title)) ? [ $task->parent_task->id => $task->parent_task->title] : [];
        }

        return view('task.create', compact('data'))->with('rec',$task);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $rules = [
          
            'title' => 'required|max:190'
        ];


        if(isset($request->component_id) && $request->component_id)
        {
            $rules['component_number'] = 'required';
        }
        $validator = Validator::make($request->all(), $rules, [
            'component_number.required' => __('form.this_field_is_required'),
        ]);

        if ($validator->fails())
        {
            return  redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $obj = Task::find($id);

        $previously_task_was_assigned_to = $obj->assigned_to;
        

        $request['start_date']      = date2sql($request->start_date) ;
        $request['due_date']        = date2sql($request->due_date)  ;
        $obj->update($request->all()) ; 


         $files                  = $request->attachment;
      
        if(is_array($files) && count($files) > 0)
        {
            $attachment = new Attachment();
            $attachment->add($files, $obj);       
        
        }

        $obj->tag_sync($request->tag_id);

         // Log Activity
        $log_name       = activity_log_name_by_componet_id($obj->component_id). $request->component_number;        
        $description    = sprintf(__('form.act_updated'), __('form.task'));            
        log_activity($obj, $description , anchor_link($obj->title, route('show_task_page', $obj->id ) ) , $log_name );  


        // Checked if the task has been assigned to a new user
        if($request->assigned_to && ($previously_task_was_assigned_to != $request->assigned_to) )
        {
            
            $member = User::find($request->assigned_to);
            $task   = $obj;

            if($member)
            {
                // Make the new user Follow the task for any changes
                $obj->add_follower(USER_TYPE_TEAM_MEMBER, $request->assigned_to);

               // Notifiy the member to who the task have been assigned
                $description    = __('form.act_assigned_task');
                $details = sprintf( 
                    __('form.act_assigned_task_to') , 
                    anchor_link($task->title, route('show_task_page',$task->id ) ), 
                    anchor_link($member->first_name . " " . $member->last_name, route('member_profile', $member->id ))

                );                
                log_activity($task, $description , $details , $log_name );       
                
                $member->notify(new TaskAssigned($task, auth()->user() ));       
                

            }
        }

        // Add the person who updated it as a follower
        $obj->add_follower(USER_TYPE_TEAM_MEMBER, auth()->user()->id);
 

        session()->flash('message', __('form.success_update'));

        if(!check_perm('tasks_view'))
        {      
           return redirect()->back();
        }
        else
        {
             return redirect()->route('show_task_page', $obj->id );
        }
       


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        DB::beginTransaction();

        try {
           
            $task->forcedelete();

            $task->delete_has_many_relations(['followers', 'attachments', 'comments']);
            
            $task->tag_sync([]);    
                    

            // Log Activity
            $log_name       = ($task->component_id == COMPONENT_TYPE_PROJECT) ? LOG_NAME_PROJECT . $task->component_number : NULL;        

            $description    = sprintf(__('form.act_deleted'), __('form.task'));       
            log_activity($task, trim($description), $task->title , $log_name );  

            session()->flash('message', __('form.success_delete'));

            DB::commit();

        } catch (\Illuminate\Database\QueryException $e) {
           // Handle Integrity constraint violation
            DB::rollback();
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
        catch (\Exception  $e) {
            
            DB::rollback();
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
                        
        }

        return  redirect()->back();
    }

    function task_related()
    {
        $search_key         = Input::get('search');
        $component_type     = Input::get('type');


        if($component_type == COMPONENT_TYPE_CUSTOMER)
        {

            $data = Customer::where('name', 'like', $search_key.'%')->get();
        }
        elseif ($component_type == COMPONENT_TYPE_LEAD)
        {
            $data = Lead::where('name', 'like', $search_key.'%')->get();
        }

        elseif ($component_type == COMPONENT_TYPE_PROJECT)
        {
            $data = Project::where('name', 'like', $search_key.'%')->get();
        }

        elseif ($component_type == COMPONENT_TYPE_PROPOSAL)
        {
            $data = Proposal::select('*', 'title AS name')->where('title', 'like', $search_key.'%')->get();
        }

        elseif ($component_type == COMPONENT_TYPE_TICKET)
        {
            $data = Ticket::select('*', 'subject AS name')->where('subject', 'like', $search_key.'%')->get();
        }

        $results = (isset($data) && $data->count() > 0) ? $data->toArray() : [];

        return response()->json([
            'results' => $results
        ]);
    }

    function parent_tasks(Request $request)
    {
        $component_id = $request->component_id ;
        $component_number = $request->component_number;
        $task_id =$request->task_id;
     

        $task = Task::select('id', 'title AS name')->orderBy('title','ASC')
        ->where('status_id', '<>', TASK_STATUS_COMPLETE)
        //->where('title', 'like', Input::get('q').'%')
        ;

        if($task_id)
        {
            $task->where('id', '<>', $task_id);
        }

        if($component_id && $component_number)
        {
            $task->where('component_id', $component_id)->where('component_number', $component_number);
        }

        $data = $task->get();

        $results = ($data->count() > 0) ? $data->toArray() : [];

        return response()->json([
            'results' => $results
        ]);
    }


    function tasks_by_component_id()
    {
        $rec['id'] = Input::get('id');
        $rec['component_id'] = Input::get('component_id');

        $returnHTML = view('task.list_by_component', compact('rec'))->render();
        return response()->json(array('status' => 1, 'html'=>$returnHTML));
    }

    function tasks_by_component_id_paginate()
    {

        $query_key = Input::get('search');
        $search_key        = $query_key['value'];
        $number_of_records = Task::all()->count();

        $query = Task::orderBy('id', 'DESC')->with(['status', 'assigned_user', 'tags', 'priority']);



        if($search_key)
        {
            $query->where('name', 'like', $search_key.'%')
                ->orWhere('rate', 'like', $search_key.'%');
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();
//

        $rec = [];

        if (count($data) > 0)
        {
            foreach ($data as $key => $row)
            {

                $rec[] = array(

                    a_links($row->title, [
                        ['action_link' => route('show_task_page', $row->id), 'action_text' => __('form.view'), 'action_class' => ''],
                        ['action_link' => route('edit_task_page', $row->id), 'action_text' => __('form.edit'), 'action_class' => ''],
                        ['action_link' => route('delete_task', $row->id), 'action_text' => __('form.delete'), 'action_class' => 'delete_item']
                    ]),
                    $row->status->name,
                    sql2date($row->start_date),
                    ($row->due_date) ? sql2date($row->due_date) : "",
                    (isset($row->assigned_user->first_name) ? $row->assigned_user->first_name . " ". $row->assigned_user->last_name : ""),
                    $row->get_tags_as_badges(true),
                    $row->priority->name,
                );

            }
        }


        $output = array(
            "draw" => intval(Input::get('draw')),
            "recordsTotal" => $number_of_records,
            "recordsFiltered" => $recordsFiltered,
            "data" => $rec
        );


        return response()->json($output);


    }

    function post_task_comment(Request $request, Task $task)
    {
        
        $message    = Input::get('comment');

        if($message && $task)
        {
            $comment = new Comment();
            $comment->body = $message;
            $comment->user_id = Auth::user()->id;
            $comment->user_type = USER_TYPE_TEAM_MEMBER;
            $task->comments()->save($comment);

           

            // Log Activity
            if($task->component_id == COMPONENT_TYPE_PROJECT) 
            {
                $project       = Project::find($task->component_number);
                $log_name      = LOG_NAME_PROJECT . $project->id;       
            }
            else
            {
                $log_name      = LOG_NAME_DEFAULT ;
            }

            $project        = Project::find($task->component_number);   
            $description    = sprintf(__('form.act_commented_on'),  __('form.task'));
            $details        = anchor_link($task->title, route('show_task_comment',  [$task->id, $comment->id ] ) );
            log_activity($comment, trim($description), $details , $log_name );  
     


            $files =  Input::get('attachment');

            if(is_array($files) && count($files) > 0)
            {
                $attachment = new Attachment();
                $attachment->add($files, $task);      
            
            }
           
            
            // Add as Follower 
            $task->add_follower(USER_TYPE_TEAM_MEMBER, auth()->user()->id);

            // Notify Followers of New Comment
            $notifiable_members = $task->get_notifiable_members(['user_type' => USER_TYPE_TEAM_MEMBER, 'user_id' => auth()->user()->id ]);        
            
            if($notifiable_members)
            {
                Notification::send($notifiable_members, new CommentOnTask($task, $comment, auth()->user() ));    
            }

            // Notify if any memeber was mentioned in the comment
            $link_to_comment = route('show_task_comment',  [$task->id, $comment->id ] );
            $comment->notify_members_of_mentions($message, $link_to_comment );

            session()->flash('message', __('form.success_posted'));
        
        }

        
        return redirect()->back();
    }


    function update_task_comment(Request $request, Task $task, Comment $comment)
    {
        $message = Input::get('comment');
       
        $comment->body = $message;
        $comment->user_id = Auth::user()->id;
        $comment->user_type = USER_TYPE_TEAM_MEMBER;
        $comment->actual_updated_at = Carbon::now()->toDateTimeString();
        $task->comments()->save($comment);  

    

        // Log Activity
        if($task->component_id == COMPONENT_TYPE_PROJECT) 
        {
            $project       = Project::find($task->component_number);
            $log_name      = LOG_NAME_PROJECT . $project->id;       
        }
        else
        {
            $log_name      = LOG_NAME_DEFAULT ;
        }

        $project        = Project::find($task->component_number);   
        $description    = sprintf(__('form.act_updated_comment_on'),  __('form.task'));
        $details        = anchor_link($task->title, route('show_task_comment',  
        [$task->id, $comment->id ] ) );
        
        log_activity($comment, trim($description), $details , $log_name ); 

        // Notify Followers
        $notifiable_members = $task->get_notifiable_members(['user_type' => USER_TYPE_TEAM_MEMBER, 'user_id' => auth()->user()->id ]);      
        
        if($notifiable_members)
        {
            Notification::send($notifiable_members, new CommentOnTask($task, $comment, auth()->user(), TRUE ));    
        }

        // Notify if any memeber was mentioned in the comment
        $link_to_comment = route('show_task_comment',  [$task->id, $comment->id ] );
        $comment->notify_members_of_mentions($message, route('show_task_page', $task->id) );      


        
        session()->flash('message', __('form.success_update'));
        return redirect()->back();
    }


    function change_status(Request $request, $task_id = NULL, $status_id = NULL)
    {
        

        if($request->ajax())
        {       
           // Request From Kanban View
           $task_id     = Input::get('task_id');
           $status_id   = Input::get('status_id');
        }
        
        $task = Task::find($task_id);               

        if($task)
        {
            
            $task->status_id = $status_id;
            $task->save();

            // Log Activity
            if($task->component_id == COMPONENT_TYPE_PROJECT) 
            {
                $project       = Project::find($task->component_number);
                $log_name      = LOG_NAME_PROJECT . $project->id;       
            }
            else
            {
                $log_name      = LOG_NAME_DEFAULT ;
            }

            $project        = Project::find($task->component_number);   
            $description    = sprintf(__('form.act_changed_status_of'),  __('form.task'));
           

            $details = sprintf(__('form.act_status_changed'), anchor_link($task->title, route('show_task_page',  
            $task->id) ), $task->status->name);
            
            log_activity($task, trim($description), $details , $log_name ); 
  

            // Notify Followers
            $notifiable_members = $task->get_notifiable_members(['user_type' => USER_TYPE_TEAM_MEMBER, 'user_id' => auth()->user()->id ]);      
            
            if($notifiable_members)
            {
                Notification::send($notifiable_members, new TaskStatusChanged($task, auth()->user() ));    
            }


            // End of Sending Notification
            

            if($request->ajax())
            {
                return response()->json(['status' => 1]);
            }
            else
            {
                return redirect()->back();
            }
           
        }
        else
        {
             if($request->ajax())
             {
                return response()->json(['status' => 2]);
             }
             else
             {
                abort(404);
             }
        
        }    
        
    }


    public function upload_attachment(Request $request)
    {
   
        $validator = Validator::make($request->all(), [
        
            'file'                    => 'required|max:1000',

        ]);

        if ($validator->fails()) 
        {
      
           return response()->json($validator->errors());
        }

        // Upload Attachment
        $attachment = Storage::putFile('public/tasks', $request->file('file') );
       
       $encrypt = encrypt([
            'name' => $attachment,
            'display_name' => $request->file->getClientOriginalName(),
        ]);



        return response()->json([
            'url' =>  asset(Storage::url($attachment)) ,
            'name' => $attachment,
            'display_name' => $request->file->getClientOriginalName(),
            'encrypted_value_for_input' => $encrypt
        ], 200);

    }


    function comments(Task $task)
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $comment_id         = Input::get('comment_id');  

        if($comment_id)
        {
            $q      = $task->comments()->where('comments.id', $comment_id);
            $query  = $task->comments()->where('comments.id', $comment_id);
        }
        else
        {
            $q      = $task->comments();
            $query  = $task->comments();
        }


        $number_of_records  = $q->get()->count();        
      

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();

        $rec = [];

        if (count($data) > 0)
        {
            foreach ($data as $key => $row)
            {

               if($row->actual_updated_at) 
               {
                    $moment = __('form.updated_at') . " " . Carbon::createFromTimeStamp(strtotime($row->actual_updated_at))->diffForHumans() ;
               }
               else
               {
                    $moment = Carbon::createFromTimeStamp(strtotime($row->created_at))->diffForHumans() ;
               }
               
               // On the user who posted the comment is allowed to edit the comment
              $action = "";

              if((Auth::user()->id == $row->user_id) && ($row->user_type == USER_TYPE_TEAM_MEMBER))
              {
                     $action = '<div class="coment_area">
                            <a href="#" class="edit_comment"><i class="far fa-edit"></i></a>
                            <a href="'. route('delete_comment', $row->id).'" class="delete_item"><i class="far fa-trash-alt"></i></a>
                            </div>
                            
                            <div style="width:100% !important;" class="comment_form">
                        <form action="'. route('patch_task_comment', [$row->commentable_id, $row->id]).'"  method="POST">
                        '.method_field('PATCH'). csrf_field().' 
                        <div class="form-group">
                            <textarea name="comment" class="form-control form-control-sm comment">'.$row->body.'</textarea>
                           </div> 
                        <button type="submit" class="btn btn-sm btn-primary">'.__('form.submit').'</button>
                        <a href="#" class="btn btn-sm btn-light cancel_commenting">'.__('form.cancel').'</a>
                        </form>
                      <div>


                            ';
              }

              if(($row->user_type == USER_TYPE_TEAM_MEMBER))
              {
                $commenter = anchor_link($row->user->name, route('member_profile', $row->user_id));
              }
              else
              {
                $commenter = anchor_link($row->user->name, route('show_customer_contact', $row->user_id));
              }
              

                $rec[] = array( 
                    '<div class="media" id="comment_'.$row->id.'">
                            
                            <img class="mr-3 staff-profile-image-small mright5" src="'.get_avatar_small_thumbnail($row->user->photo).'" />
                            <div class="media-body coment_area">
                                <div class="mt-0">'.$commenter.'</div>
                                <small class="form-text text-muted">'.$moment.'</small>                 
                            
                            <p>'.$row->parsed_comment().'</p>

                            </div>
                            
                      '.$action.'                     
                      

                    </div>
                    '


                );

            }
        }


        $output = array(
            "draw" => intval(Input::get('draw')),
            "recordsTotal" => $number_of_records,
            "recordsFiltered" => $recordsFiltered,
            "data" => $rec
        );


        return response()->json($output);


    }


    function update_task_milestone()
    {
        $task_id        = Input::get('task_id');
        $milestone_id   = Input::get('milestone_id');

        $task = Task::find($task_id);
        if($task)
        {
            $task->milestone_id =  $milestone_id;
            $task->save();

            // Log Activity
            $project        = Project::find($task->component_number);  
            $log_name       = LOG_NAME_PROJECT . $project->id;  
            $description    = sprintf(__('form.act_updated'), __('form.milestone')) ;               

            $details = anchor_link($task->title, route('show_task_page',$task->id ) ) 
                ." - " . $task->milestone->name;
            
            log_activity($task, $description , $details , $log_name ); 
        }
        
        return response()->json([$task_id,$milestone_id]);

    }


    function assign_task(Request $request, Task $task)
    {  
        $m_id = Input::get('member_id');

        if($m_id)
        {
            $member = User::find($m_id);
       
            if($member)
            {
                $task->assigned_to = $member->id;
                $task->save();

    
                // Log Activity
                if($task->component_id == COMPONENT_TYPE_PROJECT) 
                {
                    $project       = Project::find($task->component_number);
                    $log_name      = LOG_NAME_PROJECT . $project->id;       
                }
                else
                {
                    $log_name      = LOG_NAME_DEFAULT ;
                }

                $project        = Project::find($task->component_number);   
                $description    = __('form.act_assigned_task');
               

                $details = sprintf( 
                    __('form.act_assigned_task_to') , 
                    anchor_link($task->title, route('show_task_page',$task->id ) ), 
                    anchor_link($member->first_name . " " . $member->last_name, route('member_profile', $member->id ))

                );
                
                log_activity($task, $description , $details , $log_name ); 

                if($task->assigned_to != auth()->user()->id)
                {                    
                    $member->notify(new TaskAssigned($task, auth()->user() ));

                    $task->add_follower(USER_TYPE_TEAM_MEMBER, $task->assigned_to );                    
                }
                
                
                return response()->json(['status' => 1, 'msg' => __('form.success_update')]);
            }
        }
        else
        {
                $task->remove_follower(USER_TYPE_TEAM_MEMBER, $task->assigned_to);
                $task->assigned_to = NULL;
                $task->save();
                return response()->json(['status' => 1, 'msg' => __('form.success_update')]);
        }
        
        
    }


    

    
    
}

