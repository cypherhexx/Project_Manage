<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Vendor;
use App\Country;
use Illuminate\Validation\Rule;

class VendorController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {
        $data['stat'] = Vendor::statistics();

        return view('vendor.index', compact('data'));
    }


    function paginate()
    {
        $query_key = Input::get('search');
        $search_key        = $query_key['value'];

        $exclude_inactive_vendors = Input::get('exclude_inactive_vendors');

        $q = Vendor::query();
        $query = Vendor::orderBy('id', 'DESC');
           

        if($exclude_inactive_vendors)
        {
            $q->whereNULL('inactive');
            $query->whereNULL('inactive');
        }

        $number_of_records = $q->count();        


        if($search_key)
        {
            $query->where(function ($k) use ($search_key) {
                $k->where('name', 'like', $search_key.'%')
                    ->orWhere('phone', 'like', $search_key.'%')
                    ->orWhere('number', 'like', $search_key.'%')
                    ;
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



                $checked     = ($row->inactive) ? '' : 'checked';
                $rec[] = array(
                    a_links(anchor_link($row->name, route('view_vendor_page', $row->id)), [
                        [
                            'action_link' => route('edit_vendor_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission' => 'vendors_edit',
                        ],
                        [
                            'action_link' => route('delete_vendor', $row->id), 
                            'action_text' => __('form.delete'), 'action_class' => 'delete_item', 
                            'permission' => 'vendors_delete'
                        ]
                    ]),
                    $row->number ,
                    $row->contact_first_name . " ". $row->contact_last_name ,
                    $row->contact_email ,
                    $row->contact_phone,
                    ' <input '.$checked.' data-id="'.$row->id.'" class="tgl tgl-ios vendor_status" id="cb'.$row->id.'" type="checkbox"/><label class="tgl-btn" for="cb'.$row->id.'"></label>',
                    
                   	date2sql($row->created_at)

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
    public function create($lead = NULL)
    {
        $data   = Vendor::dropdowns();
        return view('vendor.main', compact('data'))->with('rec', []);
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
            'name'                      => 'required',
            'contact_first_name'        => 'required',
            'contact_last_name'         => 'required',
            'contact_email'             => 'required|email|unique:vendors',
            

        ]);

        if ($validator->fails()) 
        {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        DB::beginTransaction();
        $success = false;

        try {          

            $obj = Vendor::create($request->all());

            // Log Activity
            $description = sprintf(__('form.act_created'),  __('form.vendor')) . " ". anchor_link($obj->name, route('view_vendor_page', $obj->id ));
            log_activity($obj, $description);

            DB::commit();
            $success = true;

        } 
        catch (\Exception  $e) {
            $success = false;
            DB::rollback(); 
         
        }

        if ($success) {
            // the transaction worked ...
            session()->flash('message', __('form.success_add'));
            return redirect()->route('vendors_list');
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('vendors_list');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function profile(Vendor $vendor)
    {
        $data = [];
        
        return view('vendor.main', compact('data'))->with('rec', $vendor);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function edit(Vendor $vendor)
    {
        $data = $vendor->dropdowns();        

        return view('vendor.main', compact('data'))->with('rec',$vendor);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param    \Illuminate\Http\Request $request
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
        	'id'                      	=> 'required',
            'name'                      => 'required',
            'contact_first_name'        => 'required',
            'contact_last_name'         => 'required',
            
            'contact_email' => [
                'required',
                'email',
                Rule::unique('vendors')->ignore($request->id),
            ], 


        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        $success = false;

        try {

            $vendor = Vendor::find($id);
            $vendor->update($request->all());

            // Log Acitivity            
            $description = sprintf(__('form.act_updated'),  __('form.vendor')) . " ". anchor_link($vendor->name, route('view_vendor_page', 
                $vendor->id ));
            log_activity($vendor, $description);

            DB::commit();
            $success = true;

        } 
        catch (\Exception  $e) {
            $success = false;
            DB::rollback();    

        }

        if ($success) {
            // the transaction worked ...
            session()->flash('message', __('form.success_update'));
            return redirect()->route('vendors_list');
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('vendors_list');
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function destroy(Vendor $vendor)
    {
        DB::beginTransaction();
        $success = false;

        try {

            $vendor->forcedelete();

            // Log Acitivity
            $description    = sprintf(__('form.act_deleted'), __('form.vendor'));         
            log_activity($vendor, $description , $vendor->name); 
            
            DB::commit();
            $success = true;
            session()->flash('message', __('form.success_delete'));

        }
        catch (\Illuminate\Database\QueryException $e) {
           // Handle Integrity constraint violation
            
            session()->flash('message', __('form.delete_not_possible_fk'));
        }  
        catch (\Exception  $e) {
            $success = false;
            DB::rollback();            
        }

        return redirect()->route('vendors_list');

    }




    function change_vendor_status(Request $request)
    {
        $inactive = Input::get('inactive');
        
        $status = ($inactive == 1) ? TRUE : NULL ;

        $contact = Vendor::where('id', Input::get('id'))->update(['inactive'=> $status]);

        if(count($contact) > 0)
        {
            return response()->json(['status' => 1]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }
    }

    

}