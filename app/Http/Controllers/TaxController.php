<?php
namespace App\Http\Controllers;


use App\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaxController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {
        return view('tax.index');
    }


    function paginate()
    {

        $query_key = Input::get('search');
        $search_key        = $query_key['value'];
        $number_of_records = Tax::all()->count();


        $query = Tax::orderBy('id', 'DESC');


        if($search_key)
        {
            $query->where('name', 'like', $search_key.'%') ;
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();

        $rec = [];

        if ($data->count() > 0)
        {
            foreach ($data as $key => $row)
            {

                $rec[] = array(
                    a_links('<a class="edit_item" data-id="'.$row->id.'" href="#">'.$row->name.'</a>' , []),
                    $row->rate,
                    side_by_side_links($row->id, route('delete_tax', $row->id) )

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
            'name'        =>  'required|unique:taxes',
            'rate'        =>  'required|numeric',

        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj = new Tax();
        $obj->name              = $request->name;
        $obj->rate              = $request->rate;
        $obj->display_as        = $request->rate ."_". str_replace(" ", "_", strtolower(trim($request->name)));
        $obj->save();

        return response()->json(['status' => 1]);
    }

    public function edit(Request $request)
    {
        $obj = Tax::find(Input::get('id'));

        if($obj->count() > 0)
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
                Rule::unique('taxes')->ignore($request->id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj                    = Tax::find($request->id);
        $obj->name              = $request->name;
        $obj->rate              = $request->rate;
        $obj->display_as        = $request->rate ."_". str_replace(" ", "_", strtolower(trim($request->name)));
        $obj->save();

        return response()->json(['status' => 1]);

    }

    function destroy(Tax $obj)
    {
        $obj->forceDelete();
        session()->flash('message', __('form.success_delete'));
        return redirect()->back();
    }

}