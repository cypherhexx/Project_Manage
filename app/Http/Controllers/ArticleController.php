<?php

namespace App\Http\Controllers;

use App\Article;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('knowledge_base.index');
    }

    function paginate()
    {
        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        
        $q                  = Article::query();
        $query              = Article::orderBy('id', 'DESC')->with(['group']);

        $number_of_records  = $q->get()->count();


        if($search_key)
        {
            $query->where(function ($k) use ($search_key) {

                $k->where('name', 'like', $search_key.'%');
             });      
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
                    a_links( anchor_link($row->subject, route('knowledge_base_article_customer_view', $row->slug), TRUE ), [
                        [
                            'action_link' => route('edit_knowledge_base_article_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission' => 'Knowledge_base_edit',
                        ],
                        [
                            'action_link' => route('delete_knowledge_base_article', $row->id), 
                            'action_text' => __('form.delete'), 'action_class' => 'delete_item',
                            'permission' => 'Knowledge_base_delete',
                        ]
                    ]),
                    ($row->is_internal) ? __('form.yes') : '',
                    ($row->is_disabled) ? __('form.yes') : '',
                    $row->group->name,
                    sql2date($row->created_at),
                    

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


    public function create()
    {
        $data = Article::dropdown();
        return view('knowledge_base.create', compact('data'))->with('rec', []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param    \Illuminate\Http\Request $request
     * @return  \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [
            'article_group_id'          => 'required',
            'subject'                   => 'required',
            'slug'                      => 'required|unique:articles',      

        ];
       
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) 
        {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        DB::beginTransaction();
        $success = false;

        try {


            $obj  = Article::create($request->all()); 
               
            DB::commit();
            $success = true;

        } 
        catch (\Exception  $e) {
            $success = false;
            DB::rollback();            
        }

        if ($success) 
        {
            // the transaction worked ...
            session()->flash('message', __('form.success_add'));
           
        } 
        else 
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            
        }

         return redirect()->route('knowledge_base_article_list');
    }


    public function show($slug)
    {   

        $article = Article::where('slug', $slug)->first();        

        $data['related_articles'] = $article->related_articles;
       
       return view('customer_panel.knowledge_base.article', compact('data'))->with('article', $article);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Article $article)
    {   
        $data = Article::dropdown();
        return view('knowledge_base.create', compact('data'))->with('rec', $article );
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
            'article_group_id'          => 'required',
            'subject'                   => 'required', 
            'slug' => [
                'required',
                Rule::unique('articles')->ignore($id),
            ],  
        ]);


        if ($validator->fails())
        {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $request['is_disabled'] = ($request->is_disabled) ? 1 : NULL;
        $request['is_internal'] = ($request->is_internal) ? 1 : NULL;
        
        DB::beginTransaction();
        $success = false;

        try {

            $obj = Article::findorfail($id);
            $obj->update($request->all()) ;   


            DB::commit();
            $success = true;

        } 
        catch (\Exception  $e) {
            $success = false;

            DB::rollback();
     

        }

        if ($success)
        {
            session()->flash('message', __('form.success_update'));
            return  redirect()->route('knowledge_base_article_list');
        }
        else
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->back();

        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        $article->forcedelete();

        session()->flash('message', __('form.success_delete'));
        return redirect()->back();
    }

    
}
