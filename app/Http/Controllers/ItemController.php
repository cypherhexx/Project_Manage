<?php

namespace App\Http\Controllers;



use App\Item;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ItemController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('item.index');
    }

    function paginate()
    {
        $query_key = Input::get('search');
        $search_key        = $query_key['value'];
        $number_of_records = Item::all()->count();


        $query = Item::orderBy('id', 'DESC')
            ->with(['category'])
        ;

        if($search_key)
        {
            $query->where('name', 'like', $search_key.'%')
                ->orWhereHas('category', function ($q) use ($search_key) {
                    $q->where('item_categories.name', 'like', $search_key.'%');
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
                    a_links($row->name, [
                       [
                            'action_link' => route('edit_item_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission' => 'items_edit',
                        ],
                        [
                            'action_link' => route('delete_item_page', $row->id), 
                            'action_text' => __('form.delete'), 'action_class' => 'delete_item',
                            'permission' => 'items_delete',
                        ]
                    ]),
                    $row->description,
                    format_currency($row->rate),
                    isset($row->tax_1->rate) ? $row->tax_1->rate ."%" : '',
                    isset($row->tax_2->rate) ? $row->tax_2->rate ."%" : '',
                    $row->unit,
                    isset($row->category->name) ? $row->category->name : '',

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
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $data = Item::drop_downs();

        return view('item.create')->with('item','')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'rate' => 'required|numeric',
            'unit' => 'max:180',


        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $obj = new Item();
        $obj->name                      = $request->name ;
        $obj->item_category_id          = $request->item_category_id ;
        $obj->description               = $request->description ;
        $obj->rate                      = $request->rate ;
        $obj->unit                      = $request->unit ;
        $obj->tax_id_1                  = $request->tax_id_1 ;
        $obj->tax_id_2                  = $request->tax_id_2 ;

        $obj->save();

        session()->flash('message', __('form.success_add'));
        return redirect()->route('item_list');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {


    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Item $item)
    {
        //
        $data = Item::drop_downs();

       return view('item.create', compact('item'))->with($data);
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

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'rate' => 'required|numeric',
            'unit' => 'max:180',


        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $obj = Item::find($id);
        $obj->name                      = $request->name ;
        $obj->item_category_id          = $request->item_category_id ;
        $obj->description               = $request->description ;
        $obj->rate                      = $request->rate ;
        $obj->unit                      = $request->unit ;
        $obj->tax_id_1                  = $request->tax_id_1 ;
        $obj->tax_id_2                  = $request->tax_id_2 ;

        $obj->save();
        session()->flash('message', __('form.success_update'));
        return  redirect()->route('item_list');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Item $item)
    {
        try {

            $item->forcedelete();            
            session()->flash('message', __('form.success_delete'));

        } catch (\Illuminate\Database\QueryException $e) {
           // Handle Integrity constraint violation
            
            session()->flash('message', __('form.delete_not_possible_fk'));
        }

        return  redirect()->back();
    }


    
}
