<?php

namespace App\Http\Controllers\CustomerPanel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;

use App\Article;
use App\ArticleGroup;
use Illuminate\Support\Facades\Auth;

class KnowledgeBaseController extends Controller
{    

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $article_groups = ArticleGroup::whereNUll('parent_id')->whereNUll('is_disabled')
                                ->withCount(['articles','children'])
                                ->orderBy('sequence_number', 'ASC')->get();
        return view('customer_panel.knowledge_base.index')->with('article_groups', $article_groups);
    }

    


    public function article($slug)
    {   

        $record = Article::where('slug', $slug)->whereNUll('is_disabled')->get();

        $article = (count($record) > 0) ? $record->first() : abort(404);
    
        $data['related_articles'] = $article->related_articles;
       
       return view('customer_panel.knowledge_base.article', compact('data'))->with('article', $article);
    }


    public function category($slug)
    {

        $record = ArticleGroup::where('slug', $slug)->with(['articles', 'children'])->get();

        $category = (count($record) > 0) ? $record->first() : abort(404);

        $data = [];

        return view('customer_panel.knowledge_base.category', compact('data'))->with('category', $category);
    }
    

    public function search()
    {
        $search_key = Input::get('q');

        if(!$search_key)
        {
            abort(404);
        }

        $result = Article::where('subject', 'like', '%'.$search_key .'%')->paginate(10);

        return view('customer_panel.knowledge_base.search_result')->with('result', $result->appends(Input::except('page')) );
    }
    
}
