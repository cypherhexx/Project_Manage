<?php
namespace App\Http\Controllers;


use App\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class ReminderController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {
        return view('support.department');
    }


    function paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $q                  = Reminder::query();
        $query              = Reminder::with(['remind_to']);
                                

        $remindable_type       = Input::get('remindable_type');
        $remindable_id         = Input::get('remindable_id');
        
       

        if($remindable_id && $remindable_type)
        {
            $q->where('remindable_type', '=', 'App\\'.$remindable_type)->where('remindable_id', '=', $remindable_id); 
                
            $query->where('remindable_type', '=', 'App\\'.$remindable_type)->where('remindable_id', '=', $remindable_id);
                
        }
        

        $number_of_records = $q->count();

        if($search_key)
        {
            $query->where('description', 'like', $search_key.'%');
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
                    $row->description,                    
                    date("Y-m-d h:i A", strtotime( $row->date_to_be_notified )) ,             
                    anchor_link($row->remind_to->name, route('member_profile', $row->send_reminder_to )),
                    ($row->is_notified) ? __('form.yes') : "",
                    side_by_side_links($row->id, route('delete_reminder', $row->id) )
                    
                );

            }
        }


        $output = array(
            "draw"              => intval(Input::get('draw')),
            "recordsTotal"      => $number_of_records,
            "recordsFiltered"   => $recordsFiltered,
            "data"              => $rec
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

        $validator = Validator::make($request->all(),  [
            'remindable_type'                   =>  'required',             
            'remindable_id'                     =>  'required',
            'send_reminder_to'                  =>  'required',             
            'date_to_be_notified'               =>  'required|date_format:Y-m-d h:i A',
            'description'                       =>  'required|max:192',

        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=> $validator->errors()]);
        }

        // Saving Data
        $request['created_by'] = auth()->user()->id;
        $request['remindable_type'] = 'App\\'.$request->remindable_type ;
        $request['date_to_be_notified'] = date("Y-m-d H:i:s", strtotime($request->date_to_be_notified));

        Reminder::create($request->all());

        return response()->json(['status' => 1]);
    }

    public function edit(Request $request)
    {

        $obj = Reminder::find(Input::get('id'));

        if($obj)
        {           
            $obj->date_to_be_notified = date("Y-m-d h:i A", strtotime( $obj->date_to_be_notified ));
            return response()->json(['status' => 1, 'data' => $obj ]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }

    }


    public function update(Request $request)
    {

        $validator = Validator::make($request->all(),  [
            'id'                                =>  'required',   
            'remindable_type'                   =>  'required',             
            'remindable_id'                     =>  'required',
            'send_reminder_to'                  =>  'required',             
            'date_to_be_notified'               =>  'required|date_format:Y-m-d h:i A',
            'description'                       =>  'required|max:192',

        ]);

 

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=> $validator->errors()]);
        }

        // Saving Data
        $request['remindable_type'] = 'App\\'.$request->remindable_type ;
        $request['date_to_be_notified'] = date("Y-m-d H:i:s", strtotime($request->date_to_be_notified));

        $obj = Reminder::find(Input::get('id'));        
        $obj->update($request->all());   

        return response()->json(['status' => 1]);

    }

    function destroy(Reminder $reminder)
    {
        try {                 

             $reminder->delete();
             session()->flash('message', __('form.success_delete'));

        } catch (\Illuminate\Database\QueryException $e) {
           
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
        catch (\Exception  $e) {            
           
            session()->flash('message', __('form.could_not_perform_the_requested_action'));                        
        }

        return redirect()->back();
    }

}