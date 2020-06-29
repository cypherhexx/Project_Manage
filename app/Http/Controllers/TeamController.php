<?php

namespace App\Http\Controllers;

use App\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {

        $data = Team::dropdown();
        return view('team_member.teams', compact('data'));
    }


    function paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];

        $q                  = Team::query();
        $query              = Team::orderBy('id', 'DESC')->with(['leader', 'members']);

        if(!(check_perm('teams_view')))
        {
            // Get the Team Ids that the current user is involved in
            $sql = "SELECT team_id AS id FROM user_teams WHERE user_id = ?";

            $fetched_team_ids = DB::select($sql, [auth()->id() ]);

            if(count($fetched_team_ids) > 0)
            {
                // Converting to arrays from objects
                $fetched_team_ids = array_map(function ($value) {
                    return (array)$value;
                }, $fetched_team_ids);
            }
            else
            {
                $fetched_team_ids = [];
            }
           
            $q->whereIn('id', $fetched_team_ids);
            $query->whereIn('id', $fetched_team_ids);
        }

        $number_of_records  = $q->count();        


        if($search_key)
        {
            $query->where('name', 'like', $search_key.'%') ;
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();

        $rec = [];

        if (count($data) > 0)
        {
            $has_permission_to_edit = (check_perm('teams_edit')) ? TRUE : FALSE;
            
            foreach ($data as $key => $row)
            {
                $team_name      = $row->name;
                if($has_permission_to_edit)
                {
                    $team_name  = '<a class="edit_item" data-id="'.$row->id.'" href="#">'.$row->name.'</a>';
                }


                $rec[] = array(
                    a_links($team_name, [                       
                        [
                            'action_link' => route('delete_team', $row->id), 
                            'action_text' => __('form.delete'), 'action_class' => 'delete_item',
                            'permission' => 'teams_delete',
                        ]
                    ]),
                    (isset($row->leader)) ?anchor_link($row->leader->first_name . " " . $row->leader->last_name, "#" ) : "",
                    $row->members->count()

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
     * Store a newly created resource in storage.
     *
     * @param    \Illuminate\Http\Request  $request
     * @return  \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'        =>  'required|unique:teams',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj = new Team();
        $obj->name                  = $request->name;
        $obj->leader_user_id        = $request->leader_user_id;
        $obj->save();
        
        $member_ids = $request->member_id;

        if(is_array($member_ids) && count($member_ids) == 1 && $member_ids[0] == "")
        {
            $member_ids = "";
        }

        // If empty make the member ids variable filled with empty array
        $member_ids = ($member_ids == "") ? [] : $member_ids;

        
        // If the Team Leader is not in the team's list include him/her in the team member's list
        if($request->leader_user_id && !in_array($request->leader_user_id, $member_ids ))
        {
            if(!empty($member_ids))
            {
                array_push($member_ids, $request->leader_user_id);      
            }
            else
            {
                $member_ids = [$request->leader_user_id];
            }
            
        }        
       
        $obj->members()->attach($member_ids);

        return response()->json(['status' => 1]);
    }

    public function edit(Request $request)
    {
        $obj = Team::find(Input::get('id'));

        if($obj)
        {
            $obj->member_id = $obj->members()->pluck('user_id')->toArray();
            return response()->json(['status' => 1, 'data' => $obj->toArray()]);
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
            'name' => [
                'required',
                Rule::unique('teams')->ignore($request->id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj                        = Team::find($request->id);
        $obj->name                  = $request->name;
        $obj->leader_user_id        = $request->leader_user_id;
        $obj->save();

        $member_ids = $request->member_id;

        if(is_array($member_ids) && count($member_ids) == 1 && $member_ids[0] == "")
        {
            $member_ids = "";
        }

        // If empty make the member ids variable filled with empty array
        $member_ids = ($member_ids == "") ? [] : $member_ids;

        
        // If the Team Leader is not in the team's list include him/her in the team member's list
        if($request->leader_user_id && !in_array($request->leader_user_id, $member_ids ))
        {
            if(!empty($member_ids))
            {
                array_push($member_ids, $request->leader_user_id);      
            }
            else
            {
                $member_ids = [$request->leader_user_id];
            }
            
        }   
         
        $obj->members()->sync($member_ids);

        return response()->json(['status' => 1]);

    }

    function destroy(Team $team)
    {
        $number_of_user_attached = DB::table('user_teams')->where('team_id', $team->id)->count();

        if($number_of_user_attached == 0)
        {
            $team->members()->sync([]);
            $team->delete();
            session()->flash('message', __('form.success_delete'));
        }
        else
        {
            Session::flash('danger', __('form.team_delete_not_allowed_msg'));
        }

        return  redirect()->back();
    }
}
