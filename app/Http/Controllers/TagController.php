<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use Illuminate\Validation\Rule;
use App\Tag;

class TagController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {
        return view('tag.index');
    }

    function paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $number_of_records  = Tag::all()->count();


        $query = Tag::orderBy('name', 'ASC');

        if($search_key)
        {
            $query->where('name', 'like', like_search_wildcard_gen($search_key) );
        }

        $data = $query->get();

        $rec = [];

        if (count($data) > 0)
        {
            foreach ($data as $key => $row)
            {
                $rec[] = array(
                    a_links('<a class="edit_item" data-id="'.$row->id.'" href="#">'.$row->name.'</a>' , []),                    
                    side_by_side_links($row->id, route('delete_tag', $row->id) )

                );

            }
        }


        $output = array(
            "draw"                => intval(Input::get('draw')),
            "recordsTotal"        => $number_of_records,
            "recordsFiltered"     => count($rec),
            "data"                => $rec
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

        return view('tag.create', compact('data'))->with('rec', array());
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
            'name' => 'required|unique:tags',
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj        = new Tag();
        $obj->name  = $request->name;
        $obj->save();


        return response()->json(['status' => 1]);
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $obj = Tag::find(Input::get('id'));

        if($obj)
        {
            return response()->json(['status' => 1, 'data' => $obj->toArray()]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param    \Illuminate\Http\Request $request
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                =>  'required',
            'name' => [
                'required',
                Rule::unique('taxes')->ignore($request->id),
            ],
        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj        = Tag::find(Input::get('id'));
        $obj->name  = $request->name;
        $obj->save();


        return response()->json(['status' => 1]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function destroy(Tag $tag)
    {
        
        try {                 

            $tag->forcedelete();
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
        return redirect()->back();
        
    }

}