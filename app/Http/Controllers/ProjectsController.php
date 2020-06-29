<?php

namespace App\Http\Controllers;

use App\Notifications\ProjectStatusChanged;
use Auth;
use App\Tag;
use App\Task;
use App\User;
use App\Project;
use App\Customer;
use App\CustomerContact;

use App\Attachment;
use App\BillingType;
use App\ProjectStatus;
use App\NumberGenerator;
use App\Ticket;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;

class ProjectsController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    { 
        $data           = Project::dropdownForFiltering();
        $data['stat']   = Project::statistics();

        $data['default_status_ids'] = [ PROJECT_STATUS_NOT_STARTED, PROJECT_STATUS_IN_PROGRESS, PROJECT_STATUS_ON_HOLD ];

        return view('project.index', compact('data'));
    }


    function paginate()
    {

        $query_key                      = Input::get('search');
        $search_key                     = $query_key['value'];
        $status_ids                     = Input::get('status_ids');
        $q                              = Project::query(); 

        $query                          = Project::orderBy('id', 'DESC')->with(['customer', 'billing_type', 'status']);  

        if($status_ids)
        {
            
            $query->whereIn('status_id', $status_ids);
        } 


        /* If the current user doesn't have permission to view all projects, then 
            show only the ones that he is member of or has created.
        */
        if(!check_perm('projects_view'))
        {   
            // Get the Project Ids that the current user is involved in
            $fetched_project_ids        = Project::get_project_ids_that_the_current_user_is_involved_in();           
           
            $q->whereIn('id', $fetched_project_ids);
            $query->whereIn('id', $fetched_project_ids);
        }

        $number_of_records  = $q->count();

        if($search_key)
        {
            $query->where(function ($k) use ($search_key) {
                    $k->where('name', 'like', like_search_wildcard_gen($search_key) )                   
                    ->orWhereHas('customer', function ($q) use ($search_key) {
                        $q->where('customers.name', 'like', like_search_wildcard_gen($search_key) );
                    });
                    
                  
                   
                
            });

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
                // $tag_names        = "<span class=\"badge badge-success\">".implode('</span> <span class="badge badge-success">', $row->tags()->pluck('name')->toArray() )."</span>";

                $rec[] = array(

                    a_links(anchor_link($row->number, route('show_project_page', $row->id) ), [
                        [
                            'action_link' => route('edit_project_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission' => 'projects_edit',
                        ],
                        [
                            'action_link' => route('delete_project', $row->id), 
                            'action_text' => __('form.delete'), 'action_class' => 'delete_item',
                            'permission' => 'projects_delete',
                        ]
                    ]),
                   $row->name,
                   (check_perm('customers_view')) ? anchor_link($row->customer->name, route('view_customer_page', $row->customer_id )) : $row->customer->name ,
                   
                   sql2date($row->start_date) ,
                   ($row->dead_line) ? sql2date($row->dead_line) : '' ,
                    $row->billing_type->name,
                    $row->status->name,


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

    /**
     * Show the form for creating a new resource.
     *
     * @return  \Illuminate\Http\Response
     */
    public function create()
    {
        $data = Project::dropdown();

        return view('project.create', compact('data'))->with('rec', array());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param    \Illuminate\Http\Request $request
     * @return  \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name'                              => 'required',
            // 'prefix'                            => 'required|unique:projects,prefix',
            'customer_id'                       => 'required',
            'billing_type_id'                   => 'required',
//            'billing_rate_or_estimated_hours'   => 'required',
            'start_date'                        => 'required',
            'status_id'                         => 'required',
        ]);

        if ($validator->fails()) {

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $permissions    = ($request->permissions) ? $request->permissions : [];
        $tabs           = ($request->tabs) ? $request->tabs : [];

        DB::beginTransaction();
        $success = false;

        try {

            // Saving Data
            $obj = new Project();
            $obj->number                            = NumberGenerator::gen(COMPONENT_TYPE_PROJECT);
            $obj->name                              = $request->name;
        
            $obj->customer_id                       = $request->customer_id;        
            $obj->calculate_progress_through_tasks  = $request->calculate_progress_through_tasks;
            $obj->progress                          = $request->progress;
            $obj->billing_type_id                   = $request->billing_type_id;
            $obj->billing_rate                      = $request->billing_rate;
            $obj->start_date                        = date2sql($request->start_date);
            $obj->dead_line                         = date2sql($request->dead_line);
            $obj->description                       = $request->description;
            $obj->status_id                         = $request->status_id;
            $obj->settings                          = json_encode(['tabs' => $tabs, 'permissions' => $permissions]);
            $obj->created_by                        = auth()->id() ;
            $obj->save();


            // Attaching Members of the Project
            $member_ids                             = $request->user_id;

            if($member_ids)
            {
                if(!in_array(auth()->user()->id, $member_ids ))
                {
                    array_push($member_ids, auth()->user()->id );      
                }
            }
            else
            {
                $member_ids = [auth()->user()->id];
            }          

            $obj->members()->attach($member_ids);


            // Attaching Tags            
            if(isset($request->tag_id) && $request->tag_id)
            {
                $obj->tag_attach($request->tag_id);
            }



            // Log Activity   
            $description = sprintf(__('form.act_has_created_a_new_project'), anchor_link($obj->name, route('cp_show_project_page', $obj->id ) ));
            log_activity($obj, trim($description));


            DB::commit();
            $success = true;
        } catch (\Exception  $e) {
            $success = false;

            DB::rollback();

        }

        if ($success) {
            // the transaction worked ...
            session()->flash('message', __('form.success_add'));
            return redirect()->route('projects_list');
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('projects_list');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function show(Project $project)
    {  

        /* If the current user has no permission to view tasks, then check if this task was created by or assigned to the
            current user
        */
        if(!check_perm('projects_view'))
        { 
           
            $count = $project->members()->where('user_id', auth()->id() )->get()->count();

            if(!(($project->created_by == auth()->user()->id ) ||  $count > 0 ))
            {          
                abort(404);
            }
        }

        $group_name     = app('request')->input('group');
        $sub_group_name = app('request')->input('subgroup');

        if($group_name == 'tasks')
        {
            $data = Task::dropdown();
            $data['milestones_id_list'] = $project->milestones()->orderBy('order', 'ASC')->pluck('name', 'id')->toArray();

            if($sub_group_name == 'kanban')
            {
                $task                               = new Task(); 
                $project->data_for_kanban_view      = $task->get_data_for_kanban_view(COMPONENT_TYPE_PROJECT, $project->id);
            }
        }

        if($group_name == 'tickets')
        {
            $data           = Ticket::dropdown_for_filtering();
            $data['stat']   = Ticket::statistics_for_project($project->id);
            $data['default_ticket_status_ids'] = [ TICKET_STATUS_OPEN, TICKET_STATUS_IN_PROGRESS, TICKET_STATUS_ON_HOLD];
        }

        $data['other_projects'] = Project::where('id', '<>', $project->id)->where('status_id', TICKET_STATUS_IN_PROGRESS)->get();

        return view('project.show', compact('data'))->with('rec', $project);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function edit(Project $project)
    {
        $data = Project::dropdown();

        $project['tag_id']          = $project->tags()->pluck('tag_id')->toArray();
        $project['user_id']         = $project->members()->pluck('user_id')->toArray();
        

        if(isset($project->settings) && !empty($project->settings))
        {
            $project->settings          = json_decode($project->settings);
            $project->permissions       = $project->settings->permissions ;
            $project->tabs              = $project->settings->tabs ;
        }
        else
        {
            $project->settings                      = new \stdClass();
            $project->settings->permissions         = [] ;
            $project->settings->tabs                = [] ;
        }

        
        
        return view('project.create', compact('data'))->with('rec', $project);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param    \Illuminate\Http\Request $request
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $rules = [
            'name'                              => 'required',
            // 'prefix' => [
            //     'required',
            //     Rule::unique('projects')->ignore($id, 'id'),
            // ],
            'customer_id'                       => 'required',
            'billing_type_id'                   => 'required',
            
            'start_date'                        => 'required',
            'status_id'                         => 'required',
        ];

        $msg = [];
        if($request->billing_type_id != BILLING_TYPE_TASK_HOURS)
        {
            $rules['billing_rate']              =  'required' ;

            if($request->billing_type_id == BILLING_TYPE_FIXED_RATE)
            {
                $msg['billing_rate.required']   = sprintf(__('form.field_is_required'), __('form.total_rate'));
            }
            else
            {
                $msg['billing_rate.required']   = sprintf(__('form.field_is_required'), __('form.rate_per_hour'));
            }
        }

       
        $validator = Validator::make($request->all(), $rules, $msg);

        if ($validator->fails()) {
                return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $permissions    = ($request->permissions) ? $request->permissions : [];
        $tabs           = ($request->tabs) ? $request->tabs : [];

        DB::beginTransaction();
        $success = false;

        try {

            // Saving Data
            $obj = Project::find($id);
            $obj->name                              = $request->name;
            $obj->prefix                            = $request->prefix;
            $obj->customer_id                       = $request->customer_id;        
            $obj->calculate_progress_through_tasks  = $request->calculate_progress_through_tasks;
            $obj->progress                          = $request->progress;
            $obj->billing_type_id                   = $request->billing_type_id;
            $obj->billing_rate                      = $request->billing_rate;
            $obj->start_date                        = date2sql($request->start_date);
            $obj->dead_line                         = date2sql($request->dead_line);
            $obj->description                       = $request->description;
            $obj->status_id                         = $request->status_id;
            $obj->settings                          = json_encode(['tabs' => $tabs, 'permissions' => $permissions]);
            $obj->save();

            $obj->members()->sync($request->user_id);

            $obj->tag_sync($request->tag_id);

        


             // Log Activity   
            $description = sprintf(__('form.act_has_updated_project'), anchor_link($obj->name, route('cp_show_project_page', $obj->id ) ));
            log_activity($obj, trim($description));

            DB::commit();
            $success = true;

        } catch (\Exception  $e) 
        {
            $success = false;
            DB::rollback();
        }

        if ($success) 
        {
            // the transaction worked ...
            session()->flash('message', __('form.success_update'));
            return redirect()->route('projects_list');
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('projects_list');
        }         

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    
    public function destroy(Project $project)
    {
        //
        DB::beginTransaction();
        $success = false;

        try { 

            $project->forcedelete();     

            // Delete The Tasks
            $tasks = $project->tasks()->get();  

            foreach ($tasks as $task) 
            {
               $task->forcedelete();
            } 

            // Remove Attachments
            
            $attachments = $project->attachments()->get();  

            foreach ($attachments as $attachment) 
            {
               $attachment->delete();
            }     

             // Log Activity   
            $description = sprintf(__('form.act_deleted'), _('form.project'));
            log_activity($project, trim($description), $project->name." (".$project->number.")");        


            DB::commit();
            session()->flash('message', __('form.success_delete'));

        } 
        catch (\Illuminate\Database\QueryException $e) {
           // Handle Integrity constraint violation
             DB::rollback();
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
        catch (\Exception  $e) {
            
            DB::rollback();
            session()->flash('message', __('form.could_not_perform_the_requested_action'));

        }
        

        return redirect()->route('projects_list');

        
    }


    function get_project_by_customer_id()
    {
        $search_key     = Input::get('search');

        $customer_id    = Input::get('customer_id');

        $project        = Project::where('name', 'like', $search_key.'%');

        if($customer_id)
        {
            $project->where('customer_id', '=',  $customer_id);
        }

        $data           = $project->get();

        $results        = ($data->count() > 0) ? $data->toArray() : [];

        return response()->json(['results' => $results]);
            
        
    }

    function get_project_by_customer_contact_id()
    {
        $search_key = Input::get('search');

         $data =  CustomerContact::select(['projects.id AS id', 'projects.name AS name'])

        ->leftJoin('customers', 'customer_contacts.customer_id', '=', 'customers.id')
        ->leftJoin('projects', 'customers.id', '=', 'projects.customer_id')        
        ->where('customer_contacts.id', Input::get('customer_contact_id'))
        ->where('projects.name', 'like', $search_key.'%')
        ->get();
        $results = ($data->count() > 0) ? $data->toArray() : [];

        return response()->json([
            'results' => $results
        ]);
    }



    function get_milestones_by_project_id()
    {
        $search_key = Input::get('search');
        $project_id = Input::get('project_id');
        

        $data = Project::find($project_id)->milestones()->get();

        $results = ($data->count() > 0) ? $data->toArray() : [];

        return response()->json([
            'results' => $results
        ]);  
    }
    

    function change_status(Request $request, Project $project)
    {

        $validator = Validator::make($request->all(), [
            'project_status_id'                  => 'required',           
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $project->status_id = $request->project_status_id ;
        $project->save();

        // Log Activity   
        $description = sprintf(__('form.act_has_change_status_of_'), anchor_link($project->name, route('cp_show_project_page', $project->id ) ), $project->status->name );
        log_activity($project, trim($description));   


        $mark_all_task_as_completed = Input::get('mark_all_task_as_completed');

        if($mark_all_task_as_completed)
        {
            $tasks = $project->tasks()->get();

            foreach ($tasks as $task) 
            {
                $task->status_id = TASK_STATUS_COMPLETE;
                $task->save();
            }
        }

        $notify_project_members_status_change = Input::get('notify_project_members_status_change');
        
        if($notify_project_members_status_change)
        {
            Notification::send($project->members()->get(), new ProjectStatusChanged($project, Auth::user() ));    
        }
        

        session()->flash('message', __('form.success_update'));
        return redirect()->back();
    }


    function add_attachment(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'display_name'                       => 'required',
            'attachment'                         => 'required'              
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 , 'errors'=>$validator->errors()]);
        }

       
        $files                  = $request->attachment;
        $files                  = [current($files)];
      
        if(is_countable($files) && count($files) > 0)
        {
            $attachment = new Attachment();
            $attachment->add_single_file_with_display_name($files[0], $project, Input::get('display_name'));     
        
        }
        
        return response()->json(['status' => 1]);


    }


    

    function get_attachments(Request $request, $project_id)
    {
        $query_key              = Input::get('search');
        $search_key             = $query_key['value'];   

        $number_of_records      = Attachment::where('attachable_id', $project_id)
                                    ->where('attachable_type', Project::class)->count();       

        $query                  = Attachment::where('attachable_id', $project_id)
                                    ->where('attachable_type', Project::class)
                                    ->orderBy('display_name', 'ASC');    
      


        if($search_key)
        {
            $query->where(function ($k) use ($search_key) {
                $k->where('display_name', 'like', $search_key.'%') ;
                   
            });    
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data           = $query->get();
//

        $rec = [];

        if (count($data) > 0)
        {
            foreach ($data as $key => $row)
            {

               $person_created_route_name = ($row->user_type == USER_TYPE_CUSTOMER) ? 'view_customer_page' : 'member_profile' ;
                
                $rec[] = array(    
                    $row->display_name,
                    anchor_link($row->person_created->name , route($person_created_route_name, $row->person_created->id)),
                    $row->created_at->diffForHumans(),
                    '<a class="btn btn-secondary btn-sm" href="'. gen_url_for_attachment_download($row->name) .'"><i class="fas fa-download"></i></a>',
                    
                    delete_link(route('remove_attachment', $row->id) )
                    

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


    function invoice_project_modal_content(Project $project)
    {
        $html   = view('project.invoicing_modal')->with('rec', $project)->render();

        return response()->json(['html' => $html ]);
    }
}