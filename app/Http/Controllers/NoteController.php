<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Note;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{

   public function update(Request $request)
   {
   		$validator = Validator::make($request->all(), [
            'id'                        => 'required',
            'details'                   => 'required',             

        ]);

        if ($validator->fails()) 
        {
             return response()->json(['status' => 2 ,'errors'=>$validator->errors(), 'msg' => '' ]);
        }

        Note::where('id', Input::get('id'))->update(['body' => Input::get('details')]);  

        return response()->json(['status' => 1 , 'msg' => __('form.success_update') ]);
   }


   function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                        => 'required'    

        ]);

        if ($validator->fails()) 
        {
             return response()->json(['status' => 2 ,'errors'=> $validator->errors(), 'msg' => '' ]);
        }

        Note::where('id', Input::get('id'))->delete();  

        return response()->json(['status' => 1 , 'msg' => __('form.success_delete') ]);


    }

}
