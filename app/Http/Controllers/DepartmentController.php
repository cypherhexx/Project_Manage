<?php
namespace App\Http\Controllers;


use App\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Ticket;
use Webklex\IMAP\Client;

class DepartmentController extends Controller {

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

        $query_key = Input::get('search');
        $search_key        = $query_key['value'];
        $number_of_records = Department::all()->count();


        $query = Department::orderBy('name', 'ASC');


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
                    a_links('<a class="edit_item" data-id="'.$row->id.'" href="#">'.$row->name.'</a>' , []),
                    
                    side_by_side_links($row->id, route('delete_department', $row->id) )

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
        $rules = [
            'name'                      =>  'required|unique:departments',             
            'email'                     =>  'required|email|unique:departments',
        ];

        $enable_auto_ticket_import = Input::get('enable_auto_ticket_import');

        if($enable_auto_ticket_import)
        {
            $rules['email']         = 'required|email'; 
            $rules['imap_host']     = 'required';
            $rules['imap_password'] = 'required';    
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        Department::create($request->all());

        return response()->json(['status' => 1]);
    }

    public function edit(Request $request)
    {
        $obj = Department::find(Input::get('id'));

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

        $rules = [
            'id'                =>  'required',
            'name'              => [
                'required',
                Rule::unique('departments')->ignore($request->id),
            ],

            'email'              => [
                'required',
                'email',
                Rule::unique('departments')->ignore($request->id),
            ],

            
        ];

        $enable_auto_ticket_import = Input::get('enable_auto_ticket_import');

        if($enable_auto_ticket_import)
        {
            $rules['email']         = 'required|email'; 
            $rules['imap_host']     = 'required';
            $rules['imap_password'] = 'required';    
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=> $validator->errors()]);
        }

        // Saving Data
        $obj = Department::find(Input::get('id'));        

        $request['hide_from_client']            = (isset($request->hide_from_client)) ? $request->hide_from_client : NULL;
        $request['enable_auto_ticket_import']   = (isset($request->enable_auto_ticket_import)) ? $request->enable_auto_ticket_import : NULL;
        $request['delete_email_after_import']   = (isset($request->delete_email_after_import)) ? $request->delete_email_after_import : NULL;

        $obj->update($request->all());   

        return response()->json(['status' => 1]);

    }

    function destroy(Department $department)
    {
        try {                 

             $department->delete();
             session()->flash('message', __('form.success_delete'));

        } catch (\Illuminate\Database\QueryException $e) {
           
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
        catch (\Exception  $e) {            
           
            session()->flash('message', __('form.could_not_perform_the_requested_action'));                        
        }

        return redirect()->back();
    }


    function check_imap_connection(Request $request)
    {
        $oClient = new Client([
            'host'          => $request->imap_host,
            'port'          => $request->imap_port,
            'encryption'    => $request->imap_encryption,
            'validate_cert' => false,
            'username'      => ($request->imap_username) ?? $request->email ,
            'password'      => $request->imap_password,
            'protocol'      => 'imap',
        ]);
        /* Alternative by using the Facade
        $oClient = Webklex\IMAP\Facades\Client::account('default');
        */

        //Connect to the IMAP Server
        try{

            $oClient->connect();

            if($oClient->isConnected())
            {
                return response()->json(['status' => 1, 'msg' => __('form.connected')]);
            }
            else
            {
                return response()->json(['status' => 2, 'msg' => __('form.not_connected')]);   
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['status' => 2, 'msg' => __('form.not_connected')]);  
        }
    }
}