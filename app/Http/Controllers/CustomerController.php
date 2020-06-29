<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Currency;

use App\CustomerContact;
use App\Lead;
use App\Customer;
use App\CustomerGroup;
use App\Country;
use Illuminate\Validation\Rule;
use App\Setting;


class CustomerController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {
        $data['stat'] = Customer::statistics();

        return view('customer.index', compact('data'));
    }


    function paginate()
    {
        $query_key = Input::get('search');
        $search_key        = $query_key['value'];

        $exclude_inactive_customers = Input::get('exclude_inactive_customers');

        $q = Customer::query();
        $query = Customer::orderBy('number', 'ASC')
            ->with(['groups', 'primary_contact']);

        if($exclude_inactive_customers)
        {
            $q->whereNULL('inactive');
            $query->whereNULL('inactive');
        }

        $number_of_records = $q->count();        


        if($search_key)
        {
            $query->where('name', 'like', $search_key.'%')
                ->orWhere('phone', 'like', $search_key.'%')
                ->orWhere('number', 'like', $search_key.'%')
                ->orWhereHas('groups', function ($q) use ($search_key) {
                    $q->where('customer_groups.name', 'like', $search_key.'%');
                })
                ->orWhereHas('primary_contact', function ($q) use ($search_key) {
                    $q->where('customer_contacts.first_name', 'like', $search_key.'%')
                    ->orWhere('customer_contacts.last_name', 'like', $search_key.'%')
                    ->orWhere('customer_contacts.email', 'like', '%'.$search_key);
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



                $primary_contact = $row->primary_contact;
                $checked     = ($row->inactive) ? '' : 'checked';
                $rec[] = array(
                    a_links(anchor_link($row->name, route('view_customer_page', $row->id)), [
                        [
                            'action_link' => route('edit_customer_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission' => 'customers_edit',
                        ],
                        [
                            'action_link' => route('delete_customer', $row->id), 
                            'action_text' => __('form.delete'), 'action_class' => 'delete_item', 
                            'permission' => 'customers_delete'
                        ]
                    ]),
                    $row->number,
                    (isset($primary_contact)) ? $primary_contact->first_name . " ". $primary_contact->last_name :  "",
                    (isset($primary_contact)) ? $primary_contact->email:  "",
                    $row->phone,
                    ' <input '.$checked.' data-id="'.$row->id.'" class="tgl tgl-ios customer_status" id="cb'.$row->id.'" type="checkbox"/><label class="tgl-btn" for="cb'.$row->id.'"></label>',
                    $row->get_groups_as_badges(true),
                    date("d-m-Y", strtotime($row->created_at))

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
        $rec = [];

        // Check if the request is made to convert a lead to customer
        if($lead)
        {

            $rec = Lead::withTrashed()->find($lead);

            if(!$rec)
            {
                abort(404);
            }

            $rec->lead_id = $rec->id;
            
            if($rec->customer_id)
            {
                abort(404);
            }

            unset($rec->id);

            $rec->contact_first_name    = $rec->first_name;
            $rec->contact_last_name     = $rec->last_name;
            $rec->name                  = $rec->company;
            $rec->contact_email         = $rec->email;
            $rec->contact_phone         = $rec->phone;
            $rec->contact_position      = $rec->position;

        }

        $data   = Customer::dropdowns();
        return view('customer.create', compact('data'))->with('rec', $rec);
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
            'contact_email'             => 'required|email|unique:customer_contacts,email',
            

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

            
            $obj  = Customer::create($request->all());     
            $obj->groups()->attach($request->group_id);

            // Customer's Primary Contact
            $password           = ($request->contact_password) ? $request->contact_password : DEFAULT_USER_PASSWORD;
            $primary_contact    = CustomerContact::create([
                'customer_id'                               => $obj->id,
                'first_name'                                => $request->contact_first_name,
                'last_name'                                 => $request->contact_last_name,
                'email'                                     => $request->contact_email,
                'phone'                                     => $request->contact_phone,
                'position'                                  => $request->contact_position,
                'is_primary_contact'                        => TRUE,
                'password'                                  => Hash::make($password),
                'photo'                                     => ($request->photo) ?? NULL,                 
                'social_links'                              => ($request->social_links) ?? NULL,
                'smart_summary'                             => ($request->smart_summary) ?? NULL,

            ]);


            // If Lead was converted to Customer then Update Lead Status
            if(isset($request->lead_id) && $request->lead_id)
            {                
                $lead                   = Lead::withTrashed()->find($request->lead_id);

                if($lead)
                {
                    $lead->customer_id      = $obj->id;
                    $lead->lead_status_id   = LEAD_STATUS_CUSTOMER;
                    $lead->is_lost          = NULL;
                    $lead->deleted_at       = NULL;
                    $lead->save();
                }
                
            }
          
               
            DB::commit();
            $success = true;

             // Log Activity
            $description = __('form.act_has_created_a_new_customer'). anchor_link($obj->name, route('view_customer_page', $obj->id ));
            log_activity($obj, $description);

        } 
        catch (\Exception  $e) {
            $success = false;
            DB::rollback();   
        }

        if ($success) {
            // the transaction worked ...
            session()->flash('message', __('form.success_add'));
            return redirect()->route('customers_list');
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('customers_list');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function profile($id)
    {
        $customer = Customer::withTrashed()->findorfail($id);

        $data                       = $customer->dropdowns();
        $customer['group_id']       = $customer->groups()->pluck('group_id')->toArray();

        return view('customer.main', compact('data'))->with('rec',$customer);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customer = Customer::withTrashed()->findorfail($id);

        $data                       = $customer->dropdowns();
        $customer['group_id']       = $customer->groups()->pluck('group_id')->toArray();

        return view('customer.create', compact('data'))->with('rec',$customer);

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
            'name' => 'required',            


        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        $success = false;

        try {

             // Saving Data
            $obj = Customer::withTrashed()->findorfail($id);           

            $obj->update($request->all()) ;         

            $obj->groups()->sync($request->group_id);

            // Log Acitivity
            $description = __('form.act_has_updated_customer_profile'). anchor_link($obj->name, route('view_customer_page', $obj->id )) ;
            
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
            session()->flash('message', __('form.success_update'));
            return redirect()->route('customers_list');
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('customers_list');
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function destroy(Customer $customer)
    {
        DB::beginTransaction();
        $success = false;

        try {            
           
            $customer_contacts = $customer->contacts()->get();  

            if(count($customer_contacts) > 0)
            {
                foreach ($customer_contacts as $contacts) 
                {
                   $contacts->forcedelete();
                }  
            }              
            
            $customer->forcedelete();

             // Log Acitivity
            $description    = sprintf(__('form.act_deleted'), __('form.customer'));         
            log_activity($customer, $description , $customer->name);       



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

        return  redirect()->back();

    }

    

    function search_customer()
    {
        $search_key = Input::get('search');


        $data = Customer::where('name', 'like', $search_key.'%')->with(['primary_contact'])->get();

        if(count($data) > 0)
        {
            foreach ($data as $key=>$row)
            {
                if(isset($row->primary_contact))
                {
                    $data[$key]['contact_name']     = $row->primary_contact->full_name;
                    $data[$key]['email']            = $row->primary_contact->email;
                }
                else
                {
                    $data[$key]['contact_name']     = "";
                    $data[$key]['email']            = "";
                }

            }

        }


        $results = ($data->count() > 0) ? $data : [];

        return response()->json([
            'results' => $results
        ]);
    }


    function search_customer_contact()
    {
        $search_key = Input::get('search');


        $data = CustomerContact::select([
            'customer_contacts.id', 
            'customer_id', 
            'customer_contacts.email AS email', 
            'customers.name AS customer_name',
         DB::raw('CONCAT_WS(" ", first_name, last_name) AS name') ])
        
        ->leftJoin('customers', 'customer_contacts.customer_id', '=', 'customers.id')
        ->where('first_name', 'like', $search_key.'%')
        
        ->orWhere('last_name', 'like', $search_key.'%')
        ->orWhere('customers.name', 'like', $search_key.'%')
        ->get();
   
        $results = ($data->count() > 0) ? $data : [];

        return response()->json([
            'results' => $results
        ]);
    }




    function add_contact(Request $request, $customer_id)
    {
        $validator = Validator::make($request->all(), [
            'first_name'        => 'required',
            'last_name'         => 'required',
            'email'             => 'required|email|unique:customer_contacts',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        $number_of_contacts = CustomerContact::where('customer_id', $customer_id)->get()->count();

        // Saving Data
        $obj = new CustomerContact();
        $obj->customer_id                               = $customer_id;
        $obj->first_name                                = $request->first_name;
        $obj->last_name                                 = $request->last_name ;
        $obj->email                                     = $request->email;
        $obj->phone                                     = $request->phone;
        $obj->position                                  = $request->position;
        $obj->is_primary_contact                        = ($number_of_contacts > 0) ? FALSE : TRUE;

        if($request->password)
        {
            $obj->password                              = Hash::make($request->password) ;
        }
        $obj->save();

        return response()->json(['status' => 1]);
    }

    function update_contact(Request $request, $customer_id)
    {
        $validator = Validator::make($request->all(), [
            'id'        =>  'required',
            'first_name'        =>  'required',
            'last_name'         =>  'required',
            'email' => [
                'required',
                'email',
                Rule::unique('customer_contacts')->ignore($request->id),
            ],

        ]);

        if ($validator->fails())
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj = CustomerContact::find($request->id);
        $obj->customer_id                               = $customer_id;
        $obj->first_name                                = $request->first_name;
        $obj->last_name                                 = $request->last_name ;
        $obj->email                                     = $request->email;
        $obj->phone                                     = $request->phone;
        $obj->position                                  = $request->position;

        if($request->password)
        {
            $obj->password                              = Hash::make($request->password) ;
        }
        $obj->save();

        return response()->json(['status' => 1]);
    }

    function contacts_show()
    {
        $data = [];
        return view('customer.all_contacts', compact('data'));
    }


    function contacts_paginate()
    {
        $query_key = Input::get('search');
        $search_key        = $query_key['value'];

        $customer_id        = Input::get('customer_id');
        $q                  = CustomerContact::query();
        $query              = CustomerContact::orderBy('first_name', 'ASC');

        if($customer_id)
        {
            $q->where('customer_id', $customer_id);
            $query->where('customer_id', $customer_id);
        }

        $number_of_records  = $q->get()->count();

        if($search_key)
        {
            $query->where('first_name', 'like', $search_key.'%')
                ->orWhere('last_name', 'like', $search_key.'%')
                ->orWhere('email', 'like', $search_key.'%')
                ->orWhere('phone', 'like', $search_key.'%')
                ->orWhere('position', 'like', $search_key.'%');
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


                $checked    = ($row->inactive == TRUE) ? '' : 'checked';
                $fa_check = ($row->is_primary_contact) ?  '' : 'style="display:none"';
                $primary_contact_radio = ($row->is_primary_contact) ? 'style="display:none"' :  '';


                $rec[] = array(
                    a_links('<a class="edit_item" data-id="'.$row->id.'" href="#">'.$row->first_name . " ". $row->last_name.'</a>' , [
                        [
                            'action_link' => route('delete_customer_contact', $row->id), 
                            'action_text' => __('form.delete'), 
                            'action_class' => 'delete_item',
                            'permission' => 'customers_delete'
                        ]
                    ]),
                    $row->email,
                    $row->position,
                    $row->phone,
                    '<i class="fas fa-check" '.$fa_check.' ></i><input '.$primary_contact_radio.' class="primary_contact_radio" data-id="'.$row->id.'"  type="radio" name="is_primary_contact" value="1">',
                    ' <input '.$checked.' data-id="'.$row->id.'" class="tgl tgl-ios contact_status" id="cb'.$row->id.'" type="checkbox"/><label class="tgl-btn" for="cb'.$row->id.'"></label>',



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

    function edit_contact_details(Request $request)
    {
        $contact = CustomerContact::find(Input::get('contact_id'));

        if($contact->count() > 0)
        {
            return response()->json(['status' => 1, 'data' => $contact->toArray()]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }
    }

    function get_contact_details(Request $request)
    {
        $contact = CustomerContact::find(Input::get('contact_id'));

        if($contact->count() > 0)
        {
            $records = clone $contact;

            $records->company_name          = $contact->customer->name;
            $records->company_page_url      = route('view_customer_page', $contact->customer->id);
            $records->contact_edit_page_url = route('edit_customer_contact', [$contact->customer->id, $contact->id ]);
            $records->contact_photo_url     = ($contact->photo) ? asset(Storage::url($contact->photo)) : asset('images/user-placeholder.jpg') ;

            return response()->json(['status' => 1, 'data' => $records ]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }
    }



    function destroy_contact(CustomerContact $contact)
    {
        if($contact->is_primary_contact)
        {
            session()->flash('message', __('form.primary_contact_cannot_be_deleted'));
        } 
        elseif($contact->comments->count() > 0 || $contact->attachments->count() > 0 || $contact->tasks->count() > 0 || $contact->tickets->count() > 0)
        {
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
        else
        {

            DB::beginTransaction(); 

            try {                 

                // Remove Proposal Items
                $contact->forcedelete();               

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
        }

        

        return redirect()->back();


    }

    function change_customer_status(Request $request)
    {
        $inactive = Input::get('inactive');
        $status = ($inactive == 1) ? TRUE : NULL ;
        $contact = Customer::where('id', Input::get('id'))->update(['inactive'=> $status]);

        if(count($contact) > 0)
        {
            return response()->json(['status' => 1]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }
    }

    function change_contact_status(Request $request)
    {
        $inactive = Input::get('inactive');
        $status = ($inactive == 1) ? TRUE : NULL ;
        $contact = CustomerContact::where('id', Input::get('contact_id'))->update(['inactive'=> $status]);

        if(count($contact) > 0)
        {
            return response()->json(['status' => 1]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }
    }

    function change_primary_contact(Request $request)
    {
        $contact = CustomerContact::find(Input::get('contact_id'));


        CustomerContact::where('customer_id', $contact->customer_id )->update(['is_primary_contact'=> NULL]);

        $contact->is_primary_contact = TRUE;
        $contact->save();

        if(count($contact) > 0)
        {
            return response()->json(['status' => 1]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }
    }


    function all_contacts()
    {
        $query_key = Input::get('search');
        $search_key        = $query_key['value'];


        $q                  = CustomerContact::query();
        $query              = CustomerContact::orderBy('first_name', 'ASC')->with(['customer']);



        $number_of_records  = $q->get()->count();

        if($search_key)
        {
            $query->where('first_name', 'like', $search_key.'%')
                ->orWhere('last_name', 'like', $search_key.'%')
                ->orWhere('email', 'like', $search_key.'%')
                ->orWhere('phone', 'like', $search_key.'%')
                ->orWhere('position', 'like', $search_key.'%')
                ->orWhereHas('customer', function ($q) use ($search_key) {
                    $q->where('customers.name', 'like', $search_key.'%');
                });
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();


        $rec = [];

        if (count($data) > 0)
        {
            foreach ($data as $key => $row)
            {

                $act = [];

                $rec[] = array(                                         
                    a_links(vue_click_link($row->first_name . " ". $row->last_name , $row->id, '#'), $act),         
                    $row->email,
                    anchor_link($row->customer->name, route('view_customer_page', $row->customer->id )),
                    $row->phone,
                    $row->position
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


    function import_page()
    {

        $data['group_id_list']      = CustomerGroup::orderBy('name', 'ASC')->pluck('name', 'id')->toArray();
        return view('customer.import', compact('data'))->with('rec', "");
    }


    private function validate_customer_data($records)
    {
        $validator = Validator::make($records, [
            'name'              => 'required',
            'first_name'        => 'required',
            'last_name'         => 'required',
            'email'             => 'required|email|unique:customer_contacts',
            

        ]);

        if ($validator->fails()) 
        {
            return $validator->errors()->all();
        }

    }

    private function get_spreadsheet_writer($file_extension, $spread_sheet)
    {
        if($file_extension == 'csv')
        {
            return new \PhpOffice\PhpSpreadsheet\Writer\Csv($spread_sheet);
        }
        else
        {
            return new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spread_sheet);
        }
    }

    private function write_error_messages_in_spreadsheet($extension, $spreadsheet, $column, $message, $path)
    {
        $spreadsheet->getActiveSheet()->setCellValue($column , $message );
        $spreadsheet->getActiveSheet()->getStyle($column)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $writer = $this->get_spreadsheet_writer($extension, $spreadsheet);
        $writer->save($path); 
    }

    private function clear_all_columns_of_a_row_in_spreadsheet($spreadsheet, $path, $column_sequence_list, $row_number)
    {
        foreach ($column_sequence_list as $key=>$value) 
        {
             $spreadsheet->getActiveSheet()->setCellValue($key.$row_number , NULL);
            
        } 
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($path);
    }



    function import(Request $request)
    {

        
        $validator = Validator::make($request->all(), [        
            'file'                      => 'required|max:1000|mimes:csv,xlsx',
            
        ]);

        if ($validator->fails()) {
            return  redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $column_sequence_list = Customer::column_sequence_for_import();

        // Upload the file to a temporary directory. We will remove the file later usig cron.
        $file = Storage::putFileAs(TEMPORARY_FOLDER_IN_STORAGE, $request->file('file'), time().".".$request->file('file')->extension() );  
        $path = storage_path('app/'.$file);

        $extension      = $request->file('file')->getClientOriginalExtension();

        $reader         = ($extension == 'csv') ? new Csv() : new Xlsx();
        
        // Load the file with phpspreadsheet reader
        $spreadsheet    = $reader->load($path);  

        // Get the first active work sheet
        $worksheet      = $spreadsheet->getActiveSheet();

        // Get the highest column from the column sequeunce array. It will return a letter like: S        
        $highest_column = max(array_keys($column_sequence_list));

        // Get the next letter after the highest letter of the sequence
        $next_column_after_highest = ++$highest_column; 
        if (strlen($next_column_after_highest) > 1) 
        {   // if you go beyond z or Z reset to a or A
            $next_column_after_highest = $next_column_after_highest[0];
        }


        // Check if the number of columns in the file match with requirement
        if(strtolower($worksheet->getHighestColumn()) < $highest_column)
        {
            session()->flash('validation_errors', [__('form.number_of_columns_do_no_match')]);
            session()->flash('message', __('form.import_was_not_successfull'));
            return  redirect()->back();
        }


        if(isset($worksheet) && $worksheet)
        {     
        
            $errors = [];
    
           
            foreach ($worksheet->getRowIterator() as $indexOfRow=>$row) 
            {
               if($indexOfRow > 1)
               {             

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,                   
                    $cells = [];
                                      
                    // Get all the columns of the row into $cell array
                    foreach ($cellIterator as $column_key => $cell) 
                    {
                        if(isset($column_sequence_list[$column_key]))
                        {
                            $cells[$column_sequence_list[$column_key]] = $cell->getValue();

                        }
                    }

                    if(isset($cells['first_name']) && !$cells['first_name'])
                    {
                        continue;
                    }       
                                      

                    $error = $this->validate_customer_data($cells);

                
                  
                    if($error)
                    {                                
                        $errors[$indexOfRow] = $error;
                        $col = $next_column_after_highest.$indexOfRow;         
                        $this->write_error_messages_in_spreadsheet($extension, $spreadsheet, $col , implode(",", $error), $path);
                        
                    }
                    else
                    {
                        DB::beginTransaction();
                        $success = false;

                        try {


                            $cells['created_by']                =  auth()->user()->id;                
 
                            if($cells['country'])
                            {                       
                                $country = Country::firstOrCreate(['name' => $cells['country'] ]);
                                $cells['country_id']            = $country->id;
                            }
                            if($cells['shipping_country'])
                            {                       
                                $country = Country::firstOrCreate(['name' => $cells['shipping_country'] ]);
                                $cells['shipping_country_id']   = $country->id;
                            }
                            
                            // Create the Customer
                            $customer                       = Customer::create($cells);
                            
                            //disable activity logging
                            //$customer->disableLogging();


                            // Create Contact Person
                            $cells['customer_id']           = $customer->id;
                            $cells['phone']                 = $cells['contact_person_phone'];
                            $cells['password']              = Hash::make(($request->password) ? $request->password : DEFAULT_USER_PASSWORD );
                            $cells['is_primary_contact']    = TRUE;                            
                            CustomerContact::create($cells);                           

                          
                            // Attach Customer Groups
                            $customer->groups()->attach($request->group_id);           

                            
                            // Remove the values of the Row
                            $this->clear_all_columns_of_a_row_in_spreadsheet($spreadsheet, $path, $column_sequence_list, $indexOfRow);

                            DB::commit();
                           


                        } catch (\Exception  $e) 
                        {                  

                            DB::rollback();
                            $col = $next_column_after_highest.$indexOfRow;         
                            $this->write_error_messages_in_spreadsheet($extension, $spreadsheet, $col , __('form.system_error') , $path);
                           

                        }

                    }              
                        
               }
                
              
               
            }


      
            if(count($errors) > 0)   
            {   
                $download_link = gen_url_for_attachment_download($file);

                $message = sprintf(__('form.import_download_file_message'), anchor_link(__('form.file'), $download_link));
                session()->flash('download_file_to_see_unimported_rows', $message);   
          
                return redirect()->back();
                
            } 
            else 
            {
             
                session()->flash('message', __('form.success_add'));
                return redirect()->back();
               
            }

            
        }
        else
        {
            session()->flash('message', __('form.invalid_file_provided'));
            return redirect()->back();
        }


        
    }


    function download_sample_customer_import_file()
    {
        $filename = 'sample_customer_import_file';
        $spreadsheet = new Spreadsheet();  
        
        $Excel_writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $columns = Customer::column_sequence_for_import();

        foreach ($columns as $key=>$name) 
        {            

            $activeSheet->setCellValue($key.'1' , str_replace("_", " ", ucfirst($name) ))->getStyle($key.'1')->getFont()->setBold(true);
        }
        

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); /*-- $filename is  Xlsx filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }


    function contact_emails_by_customer_id($customer_id)
    {

        $contacts = CustomerContact::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id', 'email')->where('customer_id', $customer_id)->get();

        if(count($contacts) > 0)
        {
            return response()->json(['status' => 1 , 'data' => $contacts ] );
        }
        else
        {
            return response()->json(['status' => 2 , 'data' => [] ]);   
        }
    }


   


    function report_paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
   
        $currency_id        = Input::get('currency_id');
        $date_range         = Input::get('date_range');
        $date_from          = "";
        $date_to            = "";

        if($date_range)
        {
            list($date_from, $date_to)  = explode("-", $date_range);
            $date_from                  = str_replace('/', '-', trim($date_from) );
            $date_to                    = str_replace('/', '-', trim($date_to));
            $date_from                  = date2sql(trim($date_from));
            $date_to                    = date2sql(trim($date_to));
        }
     
        
        $common_query       = Customer::select(DB::raw('name, customers.id AS customer_id, 
                                        SUM(total) AS total_amount, count(invoices.id) AS total_invoices, IFNULL(tax_total, 0) AS tax_total') )                 

                                         ->leftJoin('invoices',function ($join) use ($date_from, $date_to, $currency_id) {
                                            $join->on('customers.id', '=', 'invoices.customer_id') ;
                                            $join->whereBetween('date', [$date_from, $date_to ]);

                                            if($currency_id)
                                            {
                                                $join->where('invoices.currency_id', $currency_id);
                                            }
                                        })

                                   
                                        ->groupBy('customers.id')
                                        ->orderBy('total_amount', 'DESC');


        $q                  = $common_query;                            

        $query              = clone $common_query;          

    
     
        $number_of_records  = $q->get()->count();

        if ($search_key)
        {
            $query->having('name', 'like', like_search_wildcard_gen($search_key))   
                    //->orhaving('total_invoices', 'like', like_search_wildcard_gen($search_key))
            ;
                
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();


        $rec = [];

        if (count($data) > 0) 
        {       
            $total_amount               = 0;
            $total_invoices             = 0;
            $total_with_tax             = 0;

            $currency                   = Currency::find($currency_id);
            $currency_symbol            = (!empty($currency)) ? $currency->symbol : NULL ;


            foreach ($data as $key => $row) 
            {
                $tax_with_total = ceil($row->total_amount + $row->tax_total);

                $rec[] = array(                   
                       
                    anchor_link($row->name, route('view_customer_page', $row->customer_id)),
                    $row->total_invoices,
                    format_currency($row->total_amount,TRUE, $currency_symbol),
                    format_currency($tax_with_total, TRUE, $currency_symbol)
                );

                $total_amount    += $row->total_amount;
                $total_invoices  += $row->total_invoices;
                $total_with_tax  += $tax_with_total;
                
            }

            array_push($rec, [

               '<b>'. __('form.total_per_page'). '<b>',
                
                '<b>'. $total_invoices . '<b>',
                '<b>'. format_currency($total_amount, TRUE, $currency_symbol). '<b>',
                '<b>'. format_currency($total_with_tax, TRUE, $currency_symbol). '<b>',

            ]);
        }


        $output = array(
            "draw" => intval(Input::get('draw')),
            "recordsTotal" => $number_of_records,
            "recordsFiltered" => $recordsFiltered,
            "data" => $rec
        );


        return response()->json($output);
    }


    function configuration_page()
    {
        $records = Setting::where('option_key', 'customer_configuration')->get();

        if(count($records) > 0)
        {
            $records = $records->first();
            $records = json_decode($records->option_value);

            $rec = new \stdClass();
            foreach ($records as $key=>$value)
            {
                $rec->{$key} = $value;
            }

        }
        else
        {
            $rec = new \stdClass();
        }

        return view('customer.configuration')->with('rec', $rec);
    }

    function update_configuration_page(Request $request)
    {

        $obj = Setting::updateOrCreate(['option_key' => 'customer_configuration' ]);
        $obj->option_value = json_encode([
            'disable_customer_registration' => Input::get('disable_customer_registration'),
        ]);
        $obj->save();

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();
    }


    

}