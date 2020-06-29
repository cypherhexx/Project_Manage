<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;


use App\CustomerGroup;
use Illuminate\Validation\Rule;

class CustomerGroupController extends Controller {

    /**
    * Display a listing of the resource.
    *
    * @return  \Illuminate\Http\Response
    */
    function index()
    {
        return view('customer.group.index');
    }


    function paginate()
    {

        $query_key = Input::get('search');
        $search_key        = $query_key['value'];
        $number_of_records = CustomerGroup::all()->count();


        $query = CustomerGroup::orderBy('id', 'DESC');


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
            foreach ($data as $key => $row)
            {

                $rec[] = array(
                    a_links('<a class="edit_item" data-id="'.$row->id.'" href="#">'.$row->name.'</a>' , []),
                    side_by_side_links($row->id, route('delete_customer_group', $row->id) )
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
        
        return view('customer.group.create', compact('data'))->with('rec', []);
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
            'name'        =>  'required|unique:customer_groups',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj = new CustomerGroup();
        $obj->name                                      = $request->name;
        $obj->save();

        return response()->json(['status' => 1]);
    }

    public function edit(Request $request)
    {
        $obj = CustomerGroup::find(Input::get('id'));

        if($obj)
        {
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
                Rule::unique('customer_groups')->ignore($request->id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj = CustomerGroup::find($request->id);
        $obj->name = $request->name;
        $obj->save();

        return response()->json(['status' => 1]);

    }

    function destroy(CustomerGroup $group)
    {
        if($group->customers->count() > 0)
        {
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
        else
        {
            $group->forcedelete();
            session()->flash('message', __('form.success_delete'));
        }
        return redirect()->back();
    }

}