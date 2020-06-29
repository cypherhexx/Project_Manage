<?php

namespace App\Http\Controllers;
use App\Project;
use App\Milestone;
use App\Rules\ValidDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class MilestoneController extends Controller
{

    function paginate($project_id)
    {

        $query_key = Input::get('search');
        $search_key        = $query_key['value'];

        $number_of_records = Milestone::where('project_id', $project_id)->count();


        $query = Milestone::where('project_id', $project_id)->orderBy('order', 'ASC');


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
                $milestone_id = (check_perm('projects_edit')) ? $row->id : '' ;
                
                $rec[] = array(

                    a_links('<a class="edit_item" data-id="'.$milestone_id.'" href="#">'.$row->name.'</a>' , [
                        [
                            'action_link' => route('delete_project_milestone', $row->id), 
                            'action_text' => __('form.delete'), 
                            'action_class' => 'delete_item',
                            'permission' => 'projects_edit',
                        ]
                    ]),

                    sql2date($row->due_date) ,

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

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name'              =>  'required',
            'project_id'        =>  'required',
            'due_date'          => ['required', new ValidDate()],
            'description'       => 'max:192',
            'order'             => 'numeric',


        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }




        // Saving Data
        $obj = new Milestone();
        $obj->project_id                                = $request->project_id;
        $obj->name                                      = $request->name;
        $obj->background_color                          = $request->background_color;
        $obj->background_text_color                     = $request->background_text_color;        
        $obj->due_date                                  = date2sql($request->due_date) ;
        $obj->description                               = $request->description;
        $obj->show_description_to_customer              = $request->show_description_to_customer;
        $obj->order                                     = $request->order;
        $obj->save();

        // Log Activity
        $project        = Project::find($obj->project_id);   
        $description    = sprintf(__('form.act_created'), __('form.milestone'));       
        $log_name       = LOG_NAME_PROJECT . $project->id;
        log_activity($obj, trim($description), $obj->name, $log_name );  


        return response()->json(['status' => 1]);

    }

    public function edit(Request $request)
    {
        $milestone = Milestone::find(Input::get('milestone_id'));

        if($milestone)
        {
            $milestone->due_date = sql2date($milestone->due_date);
            return response()->json(['status' => 1, 'data' => $milestone->toArray()]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }

    }


    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id'                =>  'required',
            'name'              =>  'required',
            'project_id'        =>  'required',
            'due_date'          => ['required', new ValidDate()],
            'description'       => 'max:192',
            'order'             => 'numeric',


        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }




        // Saving Data
        $obj = Milestone::find($request->id);
        $obj->project_id                                = $request->project_id;
        $obj->name                                      = $request->name;
        $obj->background_color                          = $request->background_color;
        $obj->background_text_color                     = $request->background_text_color;        
        $obj->due_date                                  = date2sql($request->due_date) ;
        $obj->description                               = $request->description;
        $obj->show_description_to_customer              = $request->show_description_to_customer;
        $obj->order                                     = $request->order;
        $obj->save();

        
        // Log Activity
        $project        = Project::find($obj->project_id);   
        $description    = sprintf(__('form.act_updated'), __('form.milestone'));       
        $log_name       = LOG_NAME_PROJECT . $project->id;
        log_activity($obj, trim($description), $obj->name, $log_name );


        return response()->json(['status' => 1]);

    }

    function destroy(Milestone $milestone)
    {
        if($milestone->tasks()->count() > 0)
        {
            session()->flash('message', sprintf( __('form.delete_not_allowed_in_use'), __('form.milestone') ));
           
        }
        else
        {
            $milestone->delete();

            // Log Activity
            $project        = Project::find($milestone->project_id);   
            $description    = sprintf(__('form.act_deleted'), __('form.milestone'));       
            $log_name       = LOG_NAME_PROJECT . $project->id;
            log_activity($milestone, trim($description), $milestone->name, $log_name );

           

            session()->flash('message', __('form.success_delete'));
        
        }
        return redirect()->back();
    }
}
