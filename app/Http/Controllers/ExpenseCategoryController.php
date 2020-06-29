<?php
namespace App\Http\Controllers;

use App\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;


use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {
        return view('expense.category.index');
    }


    function paginate()
    {

        $query_key = Input::get('search');
        $search_key        = $query_key['value'];
        $number_of_records = ExpenseCategory::all()->count();


        $query = ExpenseCategory::orderBy('id', 'DESC');


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
                    a_links('<a class="edit_item" data-id="'.$row->id.'" href="#">'.$row->name.'</a>' , [
//                        ['action_link' => route('view_customer_page', $row->id), 'action_text' => __('form.view'), 'action_class' => ''],
                        [
                            'action_link' => route('delete_expense_category', $row->id), 
                            'action_text' => __('form.delete'), 
                            'action_class' => 'delete_item',
                            'permission'    => 'expense_categories_delete'
                        ]
                    ]),
                    $row->description

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

        return view('expense.category.create', compact('data'))->with('rec', []);
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
            'name'        =>  'required|unique:expense_categories',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj = new ExpenseCategory();
        $obj->name       = $request->name;
        $obj->description       = $request->description;
        $obj->save();

        return response()->json(['status' => 1]);
    }

    public function edit(Request $request)
    {
        $obj = ExpenseCategory::find(Input::get('id'));

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
                Rule::unique('expense_categories')->ignore($request->id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj                    = ExpenseCategory::find($request->id);
        $obj->name              = $request->name;
        $obj->description       = $request->description;
        $obj->save();

        return response()->json(['status' => 1]);

    }

    function destroy(ExpenseCategory $obj)
    {       
        try {
            $obj->forcedelete();
            session()->flash('message', __('form.success_delete'));

        } catch (\Illuminate\Database\QueryException $e) {
           // Handle Integrity constraint violation
            
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
       
        return redirect()->back();
    }

}