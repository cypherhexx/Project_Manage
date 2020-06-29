<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\LeadStatus;
use Illuminate\Validation\Rule;

class LeadStatusController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {
        return view('lead.status.index');
    }


    function paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $number_of_records  = LeadStatus::all()->count();


        $query = LeadStatus::orderBy('id', 'ASC');



        if($search_key)
        {
            $query->where('name', 'like', $search_key.'%');
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
                if($row->is_system)
                {
                    $action = "";
                }
                else
                {
                    $action = route('delete_leads_status', $row->id);
                }

                $rec[] = array(
                    a_links('<a class="edit_item" data-name="'.$row->name.'" data-id="'.$row->id.'" href="#">'.$row->name.'</a>' , []),
                  
                     side_by_side_links(NULL, $action)

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
     * @param    \Illuminate\Http\Request $request
     * @return  \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:lead_statuses',
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj = new LeadStatus();
        $obj->name = $request->name;
        $obj->save();

        return response()->json(['status' => 1]);
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
        $validator = Validator::make($request->all(), [
           
            'name'         => [
                                'required',
                                Rule::unique('lead_statuses')->ignore($id),
            ],
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj = LeadStatus::find($id);
        $obj->name = $request->name;
        $obj->save();


        return response()->json(['status' => 1]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function destroy(LeadStatus $status)
    {
        if($status->is_system)
        {
            abort(404);
        }

        try {                 

            $status->forcedelete();
            session()->flash('message', __('form.success_delete'));
        
        } catch (\Illuminate\Database\QueryException $e) {
           // Handle Integrity constraint violation
            DB::rollback();
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
        catch (\Exception  $e) {
            
            DB::rollback();
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
                        
        }    
        
        
        return redirect()->route('leads_statuses_list');
    }

}