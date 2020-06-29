<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Tag;
use App\Task;
use App\User;
use App\Project;
use App\Customer;
use App\Milestone;
use App\Invoice;
use App\Estimate;
use App\BillingType;
use App\ProjectStatus;
use App\TimeSheet;
use App\Attachment;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProjectsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {   
        
            
        $data['stat'] = Project::statistics(Auth::user()->customer_id);

        
        return view('customer_panel.project.index', compact('data'));
    }


    function paginate()
    {

        $query_key = Input::get('search');
        $search_key        = $query_key['value'];
        $number_of_records = Project::where('customer_id',  Auth::user()->customer_id )->count();


        $query = Project::where('customer_id', Auth::user()->customer_id )
            ->orderBy('id', 'DESC')
            ->with(['members', 'billing_type', 'status'])
        ;

        if($search_key)
        {
            $query->where('name', 'like', like_search_wildcard_gen($search_key) )
                ->orWhere('start_date', 'like', like_search_wildcard_gen( date2sql($search_key)) )
                ->orWhere('dead_line', 'like', like_search_wildcard_gen( date2sql($search_key)) )                
                ->orWhereHas('billing_type', function ($q) use ($search_key) {
                    $q->where('billing_types.name', 'like', like_search_wildcard_gen($search_key) );
                })
                ->orWhereHas('status', function ($q) use ($search_key) {
                    $q->where('project_statuses.name', 'like', like_search_wildcard_gen($search_key) );
                })
                ->orWhereHas('tags', function ($q) use ($search_key) {
                    $q->where('tags.name', 'like', like_search_wildcard_gen($search_key) );
                })
            ;


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
                $tag_names        = "<span class=\"badge badge-success\">".implode('</span> <span class="badge badge-success">', $row->tags()->pluck('name')->toArray() )."</span>";

                $rec[] = array(

                  anchor_link($row->name, route('cp_show_project_page', $row->id)),            
                  sql2date($row->start_date) ,
                  sql2date($row->dead_line),
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
     * Display the specified resource.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function show(Project $project)
    {

        if($project->customer_id != Auth::user()->customer_id)
        {
            return abort(404);
        }

        $group_name     = app('request')->input('group');
        $sub_group_name = app('request')->input('subgroup');

        if(isset($project->settings) && !empty($project->settings))
        {
            $project->settings          = json_decode($project->settings);
        }
        else
        {
            $project->settings = new \stdClass();

            $project->settings->permissions       = [] ;
            $project->settings->tabs              = [] ;
        }


        $data = Task::dropdown();
        $data['milestones_id_list'] = $project->milestones()->orderBy('order', 'ASC')->pluck('name', 'id')->toArray();

        if($sub_group_name == 'kanban')
        {
            $task                               = new Task(); 
            $project->data_for_kanban_view      = $task->get_data_for_kanban_view(COMPONENT_TYPE_PROJECT, $project->id);
        }
        else if($group_name == 'tasks' && $sub_group_name == 'details')
        {
            
            if(!check_customer_project_permission($project->settings->permissions, 'view_tasks')) 
            {
                abort(404);
            }

            $project->task_details              = Task::find(app('request')->input('task_')); 

            if(empty($project->task_details) ||  $project->task_details->component_number != $project->id)
            {
                abort(404);
            }
           
        }
        
     

 
        return view('customer_panel.project.show', compact('data'))->with('rec', $project);

    }



    function paginate_milestone(Project $project)
    {

        if($project->customer_id != Auth::user()->customer_id)
        {
            return abort(404);
        }
        
        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];

        $number_of_records  = Milestone::where('project_id', $project->id)->count();        


        $query              = Milestone::where('project_id', $project->id)->orderBy('order', 'ASC');
        


        if($search_key)
        {
            $query->where('name', 'like', like_search_wildcard_gen($search_key) )
                ->orWhere('start_date', 'like', like_search_wildcard_gen( date2sql($search_key)) )
            ;

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

                $rec[] = array($row->name,sql2date($row->due_date));

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


    function paginate_timesheet(Project $project)
    {

        if($project->customer_id != Auth::user()->customer_id)
        {
            return abort(404);
        }

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $component_number   = Input::get('component_number');
        $component_id       = Input::get('component_id');
        $task_id            = Input::get('task_id');

        $q = TimeSheet::select('time_sheets.*', 'tasks.title AS task_title', 'users.first_name', 'users.last_name', 'tasks.is_billable', 'tasks.component_id', 'projects.billing_type_id')
        ->join('tasks', 'time_sheets.task_id' , '=', 'tasks.id')
        ->join('users', 'time_sheets.user_id' , '=', 'users.id')
        ->join('projects', 'tasks.component_number' , '=', 'projects.id')
                ->where('tasks.component_number', $project->id)
                ->where('tasks.component_id', COMPONENT_TYPE_PROJECT )
                   
        ->orderBy('time_sheets.id', 'DESC')
        ;


      
        if($task_id != "")
        {
            $q->where('time_sheets.task_id', $task_id);     
        }
        

        $query = $q;
        
        $number_of_records  =  $q->get()->count();        


        if($search_key)
        {
           $query->where('tasks.title', 'like', $search_key.'%')
                ->orwhere('users.first_name', 'like', $search_key.'%')
               ->orWhere('users.last_name', 'like', $search_key.'%')
               ;


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
                $start_time = Carbon::createFromFormat('Y-m-d H:i:s' , $row->start_time);
                $end_time   = Carbon::createFromFormat('Y-m-d H:i:s' , $row->end_time);
                

                $is_billed = "";

                if(($row->billing_type_id == BILLING_TYPE_TASK_HOURS) || ($row->is_billable  && !$row->component_id))
                {
                    if($row->invoice_id)
                    {
                        $is_billed = '<a href="'. route('invoice_link', $row->invoice_id) .'"><span class="badge badge-success">' .__('form.invoiced') . '</span></a>';
                    }
                    else
                    {
                        $is_billed =  '<span class="badge badge-warning">' . __('form.not_billed') . '</span>';
                    }
                }
                
                
                $rec[] = array(
                    $row->first_name . " ". $row->last_name,
                    anchor_link($row->task_title, route('cp_show_task_page', [$project->id, $row->task_id] )),
                    $start_time->format("d-m-Y h:i a"),
                    $end_time->format("d-m-Y h:i a"),                   
                    $row->duration . "<br>". $is_billed ,
                   
                    

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


    function paginate_invoices()
    {
        

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $customer_id        = Auth::user()->customer_id ;
        $project_id         = Input::get('project_id');
        $q                  = Invoice::where('customer_id', $customer_id)
                                ->whereNotIn('status_id', [INVOICE_STATUS_DRAFT, INVOICE_STATUS_CANCELED]);
        $query              = Invoice::where('customer_id', $customer_id)
                                ->orderBy('id', 'DESC')->with(['status'])
                                ->whereNotIn('status_id', [INVOICE_STATUS_DRAFT, INVOICE_STATUS_CANCELED]);

        if($project_id)
        {
            $q->where('project_id', $project_id);
            $query->where('project_id', $project_id);
        }
        

        $number_of_records  = $q->get()->count();

        if ($search_key)
        {
            $query->where('number', 'like', like_search_wildcard_gen($search_key))
                ->orWhere('total', 'like', like_search_wildcard_gen($search_key))
                ->orWhere('tax_total', 'like', like_search_wildcard_gen($search_key))
                ->orWhere('date', 'like', like_search_wildcard_gen(date2sql($search_key)))
                ->orWhere('due_date', 'like', like_search_wildcard_gen(date2sql($search_key)))

                ->orWhereHas('status', function ($q) use ($search_key) {
                    $q->where('name', 'like', $search_key . '%');
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
                    $rec[] = array(

                    // a_links(vue_click_link($row->number, $row->id, route('invoice_list'). '?id='.$row->id), $act),
                    anchor_link($row->number, route('invoice_customer_view', [ $row->id, $row->url_slug ]), TRUE),   
                    format_currency($row->total),
                    format_currency($row->tax_total),                                     
                    isset(($row->date)) ? sql2date($row->date) : "",
                    isset(($row->due_date)) ? sql2date($row->due_date) : "",
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


    function paginate_estimates()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $project_id         = Input::get('project_id');

        $customer_id        = Auth::user()->customer_id ;
        $q                  = Estimate::where('customer_id', $customer_id)
                                ->whereNotIn('status_id', [ESTIMATE_STATUS_DRAFT, ESTIMATE_STATUS_EXPIRED, ESTIMATE_STATUS_DECLINED ]);
        $query              = Estimate::where('customer_id', $customer_id)
                            ->whereNotIn('status_id', [ESTIMATE_STATUS_DRAFT, ESTIMATE_STATUS_EXPIRED, ESTIMATE_STATUS_DECLINED ])
                            ->orderBy('id', 'DESC')->with(['status']);

        if($project_id)
        {
            $q->where('project_id', $project_id);
            $query->where('project_id', $project_id);
        }

        $number_of_records  = $q->get()->count();

        if($search_key)
        {
            $query->where('number', 'like', like_search_wildcard_gen($search_key))
                ->orWhere('total', 'like', like_search_wildcard_gen($search_key))
                ->orWhere('tax_total', 'like', like_search_wildcard_gen($search_key))
                ->orWhere('date', 'like', like_search_wildcard_gen(date2sql($search_key)))
                ->orWhere('expiry_date', 'like', like_search_wildcard_gen(date2sql($search_key)))
           
                ->orWhereHas('status', function ($q) use ($search_key) {
                    $q->where('name', 'like', $search_key.'%');
                })
            ;
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

                    anchor_link($row->number, route('estimate_customer_view', [$row->id, $row->url_slug] ), TRUE ),
                    sql2date($row->date),
                    sql2date($row->expiry_date),
                    format_currency($row->total),
                    $row->reference,
                    $row->status->name                  

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
      
        if(!empty($files))
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

               
                
                $rec[] = array(    
                    $row->display_name,
                    $row->person_created->name,
                    $row->created_at->diffForHumans(),
                    '<a class="btn btn-secondary btn-sm" href="'. gen_url_for_attachment_download($row->name) .'"><i class="fas fa-download"></i></a>',
                    
                    ($row->user_type == USER_TYPE_CUSTOMER && $row->person_created->id == auth()->user()->id) ? delete_link(route('remove_attachment', $row->id) ) : ''
                    

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


    public function destroy($project)
    {
        try {
      
            $project->forcedelete();
            
            session()->flash('message', __('form.success_delete'));

        } catch (\Illuminate\Database\QueryException $e) {
           // Handle Integrity constraint violation
            
            session()->flash('message', __('form.delete_not_possible_fk'));
        }

        return  redirect()->back();
    }


}