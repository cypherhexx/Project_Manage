<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ToDo;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class ToDoController extends Controller
{
    function get_all()
    {
  		$to_dos = ToDo::where('user_id', auth()->user()->id)->get();
  		$to_dos = (count($to_dos) > 0) ? $to_dos->toArray() : [];
  		return response()->json($to_dos);    	
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
			       'text' => 'required',
        ]);


        if (!$validator->fails()) 
        {
          $todo = new ToDo();
          $todo->user_id 	= auth()->user()->id;
          $todo->text 		= Input::get('text');
          $todo->save();
        }

        return $this->get_all();
    }


    public function change_status(Request $request)
    {

        $validator = Validator::make($request->all(), [
			       'id' => 'required',
        ]);


        if (!$validator->fails()) 
        {
          return ToDo::where( 'id',Input::get('id'))->update(['completed' => Input::get('completed') ]);
         
        }

        return FALSE;

    }


    public function destory(Todo $todo)
    {
    	 $todo->delete();       
    }

    public function destory_all_completed()
    {
    	Todo::where('user_id', auth()->user()->id)->where('completed', TRUE)->delete();    

    	return $this->get_all();   
    }


}
