<?php
namespace App\Http\Controllers;


use App\ArticleGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ArticleGroupController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {
        return view('knowledge_base.group.index');
    }


    function paginate()
    {

        $query_key = Input::get('search');
        $search_key        = $query_key['value'];
        $number_of_records = ArticleGroup::all()->count();


        $query = ArticleGroup::orderBy('sequence_number', 'ASC');

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
                    ($row->is_disabled) ? __('form.yes') : '',
                    side_by_side_links($row->id, route('delete_knowledge_base_article_group', $row->id) )

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
            'name'                      =>  'required|unique:article_groups',
            'sequence_number'            =>  'nullable|numeric',
            

        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        $request['slug'] = str_slug($request->name, '-');


        // Saving Data
        ArticleGroup::create($request->all());     
        
        return response()->json(['status' => 1]);
    }

    public function edit(Request $request)
    {
        $obj = ArticleGroup::find(Input::get('id'));

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
                Rule::unique('article_groups')->ignore($request->id),
            ],
            'sequence_number'            =>  'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }
        
        $request['slug'] = str_slug($request->name, '-');
        $request['is_disabled'] = ($request->is_disabled) ? 1 : NULL;
        
        // Saving Data
        $obj  = ArticleGroup::find($request->id);
      	$obj->update($request->all()) ; 

        return response()->json(['status' => 1]);

    }

    function destroy(ArticleGroup $group)
    {

        DB::beginTransaction();
       
        try {
                $group->forcedelete();          

                session()->flash('message', __('form.success_delete'));  

                DB::commit();                
        
        } catch (\Illuminate\Database\QueryException $e) {
           // Handle Integrity constraint violation
            DB::rollback();
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
        catch (\Exception  $e) {
            
            DB::rollback();
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
                        
        }

        return redirect()->route('knowledge_base_article_group_list');

    }

}