<?php

namespace App\Http\Controllers;

use App\Customer;
use App\EstimateStatus;
use App\Invoice;
use App\Item;
use App\Lead;
use App\NumberGenerator;
use App\Estimate;
use App\EstimateItem;
use App\Proposal;
use App\Tax;
use App\CustomerContact;
use App\User;
use App\Setting;
use App\Services\CommonSalesJobs;
use App\Services\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEstimate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EstimateAccepted;
use App\Notifications\EstimateDeclined;

class EstimateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currency_id        = config('constants.default_currency_id');
    
        $estimate_statuses = [
            ESTIMATE_STATUS_DRAFT           => 0, 
            ESTIMATE_STATUS_SENT            => 0, 
            ESTIMATE_STATUS_EXPIRED         => 0, 
            ESTIMATE_STATUS_DECLINED        => 0,
            ESTIMATE_STATUS_ACCEPTED        => 0, 
        ];


        $unaccepted_estimates = Estimate::where('currency_id', $currency_id)
        ->selectRaw('count(id) AS number_of_records, status_id, IFNULL(SUM(total), 0) AS total_by_status ')
        ->groupBy('status_id')->get();

        $total               = 0;

        if(count($unaccepted_estimates) > 0)
        {
            foreach ($unaccepted_estimates as $unaccepted_estimate) 
            {
                $estimate_statuses[$unaccepted_estimate->status_id] = $unaccepted_estimate->toArray();
                $total   += $unaccepted_estimate->number_of_records;
            }

            
        }

       

        // Calculate the Percentage 
        foreach ($estimate_statuses as $key => $row) 
        {   
            if(is_array($estimate_statuses[$key]))
            {
                $estimate_statuses[$key] = [
                    'percent'   => ($total == 0) ? 0 : round(($row['number_of_records'] / $total) * 100), 
                    'number'    => $row['number_of_records'],
                    'total'     => $row['total_by_status'],               
                ];
            }
            else
            {
                // Not an array because no record was found in the table
                $estimate_statuses[$key] = [
                    'percent'   => 0, 
                    'number'    => 0, 
                    'total'     => 0               
                ];
            }
            
        }
      

        $data['stat_estimates']              = $estimate_statuses;
     
        $data['stat_total_estimates']        = $total;


        $rec['item_statuses'] = EstimateStatus::all();

        $data['estimate_statuses_id_list']   = EstimateStatus::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        $data['default_estimate_status_id_list'] = [
            ESTIMATE_STATUS_DRAFT, 
            ESTIMATE_STATUS_SENT    , 
            ESTIMATE_STATUS_EXPIRED , 
            ESTIMATE_STATUS_DECLINED,
           
        ];

        if($email = get_setting('email_template_estimate_sent_to_customer'))
        {
            $data['email_template'] = (isset($email->template)) ? $email->template : "";
        }
        else
        {
           $data['email_template']  = "";
        }



        return view('estimate.index', compact('data'))->with('rec', $rec);
    }

    function paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $customer_id        = Input::get('customer_id');
        $status_id          = Input::get('status_id');
        $q                  = Estimate::query();
        $query              = Estimate::orderBy('id', 'DESC')->with(['status', 'tags', 'customer', 'project']);


        // If the user has permission to view only the ones that are created by himself;
        if(!check_perm('estimates_view') && check_perm('estimates_view_own'))
        {
            $q->where(function($k){
                $k->where('created_by', auth()->user()->id);
            });
            $query->where(function($k){
                $k->where('created_by', auth()->user()->id);
            });                   
            
        }

        if($customer_id)
        {
            $q->where('customer_id', $customer_id);
            $query->where('customer_id', $customer_id);
        }


        // Data Filtering
        if($status_id)
        {
            $query->whereIn('status_id', $status_id);
        }

        // End of data Filtering


        $number_of_records  = $q->get()->count();

        if($search_key)
        {
            $query->where(function ($k) use ($search_key) {
                $k->where('number', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('total', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('tax_total', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('date', 'like', like_search_wildcard_gen(date2sql($search_key)))
                    ->orWhere('expiry_date', 'like', like_search_wildcard_gen(date2sql($search_key)))
                    ->orWhere('reference', 'like', like_search_wildcard_gen($search_key))
                    ->orWhereHas('customer', function ($q) use ($search_key) {
                        $q->where('customers.name', 'like', $search_key.'%');
                    })
                    ->orWhereHas('tags', function ($q) use ($search_key) {
                        $q->where('name', 'like', $search_key.'%');
                    })
                    ->orWhereHas('status', function ($q) use ($search_key) {
                        $q->where('name', 'like', $search_key.'%');
                    })
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

                $rec[] = array(

                    a_links(vue_click_link($row->number, $row->id ) , [
                        [
                            'action_link' => route('estimate_customer_view', [$row->id, $row->url_slug ]), 
                            'action_text' => __('form.view'), 'action_class' => '', 'new_tab' => TRUE,
                            'permission' => 'estimates_view',
                        ],
                        [
                            'action_link' => route('edit_estimate_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission' => 'estimates_edit',
                        ],
                       // ['action_link' => route('delete_estimate', $row->id), 'action_text' => __('form.delete'), 'action_class' => 'delete_item']
                    ]),

                    format_currency($row->total, TRUE, $row->get_currency_symbol()),
                    format_currency($row->tax_total, TRUE, $row->get_currency_symbol()),
                    sql2date($row->date),
                    anchor_link($row->customer->name, route('view_customer_page', $row->customer_id)),
                    isset($row->project->name) ? anchor_link($row->project->name, route('show_project_page', $row->project->id)) : "",
                    $row->get_tags_as_badges(true),                    
                    sql2date($row->expiry_date),
                    $row->reference,
                    $row->status->name,

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
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $data = Estimate::dropdown();
        $rec = new \stdClass();
        $rec->currency_id = config('constants.default_currency_id');
        $rec->terms_and_condition   = get_setting('terms_estimate');
        return view('estimate.create', compact('data'))->with('rec', $rec);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [
            'customer_id' => 'required',
            'currency_id' => 'required',
            'date' => 'required',
        ];
        $msg = [
            'customer_id.required' => sprintf(__('form.field_is_required'), __('form.customer')) ,
            'currency_id.required' => sprintf(__('form.field_is_required'),__('form.currency')) ,
            'items.*.description.required'      => __('form.required'),
            'items.*.quantity.required'         => __('form.required'),
            'items.*.rate.required'             => __('form.required'),

        ];


        if (isset($request->items) && !empty($request->items)) 
        {
            $more_rules = [
                'items.*.description'   => 'required',
                'items.*.quantity'      => 'required',
                'items.*.rate'          => 'required',

            ];
            
            $rules = $rules + $more_rules;
        }

        $validator = Validator::make($request->all(), $rules, $msg);


        if ($validator->fails()) 
        {

            $request_all = CommonSalesJobs::populate_item_line_data($validator->errors(), $request);            

            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request_all)->with(['rec' => $request_all, 'data' => $request_all]);
        }

        DB::beginTransaction();
        $success = false;

        try {             

                $request['url_slug']            = md5(microtime());        
                $request['number']              = NumberGenerator::gen(COMPONENT_TYPE_ESTIMATE);         
                $request['date']                = date2sql($request->date) ;
                $request['expiry_date']         = ($request->expiry_date) ? date2sql($request->expiry_date) : NULL ;
                $request['tax_total']           = calculate_tax_total($request->taxes);
                $request['taxes']               = (!empty($request->taxes)) ? json_encode($request->taxes) : NULL;
                
                $request['created_by']          = auth()->user()->id;

                // Create the Estimate
                $obj = Estimate::create($request->all());

                // Inserting Product Items in proposal_items table
                $common_sales_jobs = new CommonSalesJobs($obj);
                $common_sales_jobs->insert_item_line($request, EstimateItem::class ,'estimate_id');

                    
                // Attach the tags
                $obj->tag_attach($request->tag_id);

                

                // Change Proposal Status if it was converted to Estimate
                if(isset($request->proposal_id) && $request->proposal_id)
                {
                    $proposal = Proposal::find($request->proposal_id);
                    $proposal->converted_to = COMPONENT_TYPE_ESTIMATE;
                    $proposal->converted_to_id = $obj->id;
                    $proposal->status_id = PROPOSAL_STATUS_ACCEPTED;
                    $proposal->save();
                }



            // Log Activity
            $description = sprintf(__('form.act_created') , __('form.estimate') );            
            log_activity($obj, $description,  anchor_link($obj->number, route('show_estimate_page', $obj->id )) );

            DB::commit();
            $success = true;

        } catch (\Exception  $e) {
            $success = false;

            DB::rollback();
            

        }

        if ($success) {
            // the transaction worked ...
            session()->flash('message', __('form.success_add'));
            return redirect()->route('estimate_list');
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('estimate_list');
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Estimate $estimate)
    {
        return view('estimate.show', compact('data'))->with('rec', $estimate);

    }

    
    public function edit(Estimate $estimate)
    {


        $data = Estimate::dropdown();

        $common_sales_jobs = new CommonSalesJobs($estimate);

        // Merging Tax Dropdown Information
        $data['tax_id_list']    = $common_sales_jobs->merge_tax_dropdown_information($data['tax_id_list']);

        $estimate->items        = $estimate->item_line()->get();
        $estimate->tag_id       = $estimate->tags()->pluck('tag_id')->toArray();


        $data['customer_id_list'] = [ $estimate->customer->id => $estimate->customer->name] ;

        if($estimate->project_id)
        {
          $data['project_id_list']    = [$estimate->project->id => $estimate->project->name];  
        }


        return view('estimate.create', compact('data'))->with('rec',$estimate );
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


        $rules = [
            'customer_id' => 'required',
            'currency_id' => 'required',
            'date' => 'required',
        ];
        $msg = [
            'customer_id.required' => sprintf(__('form.field_is_required'), __('form.customer')) ,
            'currency_id.required' => sprintf(__('form.field_is_required'),('form.currency')) ,
            'items.*.description.required'      => __('form.required'),
            'items.*.quantity.required'         => __('form.required'),
            'items.*.rate.required'             => __('form.required'),

        ];

        if (isset($request->items) && !empty($request->items)) 
        {
            $more_rules = [
                'items.*.description'   => 'required',
                'items.*.quantity'      => 'required',
                'items.*.rate'          => 'required',

            ];
            $rules = $rules + $more_rules;

        }


        $validator = Validator::make($request->all(), $rules, $msg);

        if ($validator->fails()) 
        {

            $request_all = CommonSalesJobs::populate_item_line_data($validator->errors(), $request);            

            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request_all)->with(['rec' => $request_all, 'data' => $request_all]);
        }


        DB::beginTransaction();
        $success = false;

        try {


            $obj                            = Estimate::find($id);   
            $request['date']                = date2sql($request->date) ;
            $request['expiry_date']         = ($request->expiry_date) ? date2sql($request->expiry_date) : NULL ;
            $request['tax_total']           = calculate_tax_total($request->taxes);
            $request['taxes']               = (!empty($request->taxes)) ? json_encode($request->taxes) : NULL;
            

            // Update Estimate
            $obj->update($request->all()) ;

            // Update Item Line
            $common_sales_jobs = new CommonSalesJobs($obj);
            $common_sales_jobs->update_item_line($request->items, EstimateItem::class, 'estimate_id');
            
            $obj->tag_sync($request->tag_id);            
            
            // Log Activity
            $description = sprintf(__('form.act_updated'), __('form.estimate'));             
            log_activity($obj, $description, anchor_link($obj->number, route('show_estimate_page', $obj->id )));

            DB::commit();
            $success = true;
        } catch (\Exception  $e) {
            $success = false;

            DB::rollback();
           debug($e);
        }

        if ($success) {
            // the transaction worked ...
            session()->flash('message', __('form.success_update'));
            return redirect()->route('estimate_list');
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('estimate_list');
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Estimate $estimate)
    {
        DB::beginTransaction();
       
        try {
                // Remove Estimate Items
                $items = $estimate->item_line()->get();  

                foreach ($items as $item) 
                {
                   $item->forcedelete();
                }

                 // Remove Tags
                $estimate->tag_sync([]);
             

                $estimate->forcedelete();           

                // Log Acitivity
                $description    = sprintf(__('form.act_deleted'), __('form.estimate'));                
                log_activity($estimate, trim($description), $estimate->number ); 

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

        return redirect()->route('estimate_list');
    }



    function get_estimate_details_ajax()
    {
        $id = Input::get('id');

        $rec = Estimate::find($id);

        $rec->array_of_taxes_used = [];
        
        if(isset($rec->taxes) && $rec->taxes)
        {
            $rec->array_of_taxes_used = json_decode($rec->taxes);

        }

        // If Estimate was converted to Invoice
        if(($rec->status_id == ESTIMATE_STATUS_ACCEPTED) && ($rec->invoice_id))
        {
            $url = route('invoice_list') ;
            $conv_obj = Invoice::find($rec->invoice_id);      

            // Link to Converted Invoice
            if(isset($conv_obj->number))
            {
                $rec->link_to_converted_component = $url."?id=". $rec->invoice_id;
                $rec->link_text = $conv_obj->number;
                $rec->hide_status_dropdown = TRUE;
                $rec->hide_convert_to_button = TRUE;
            }
        }
        else
        {
            $rec->hide_status_dropdown = FALSE;
            $rec->hide_convert_to_button = FALSE;
            $rec->link_to_converted_component = "";
            $rec->link_text = "";
        }

        $returnHTML = view('estimate.partials.show.estimate', compact('rec'))->render();
        return response()->json(
            array(
                'status' => 1,
                'html'=> $returnHTML,
                'records' => $rec,
                'item_status' => [
                    'name' => $rec->status->name,
                    'id' => $rec->status_id
                ],
                'url_to_customer_view' => route('estimate_customer_view', [$id, $rec->url_slug ])
            )
        );
    }


    function change_status()
    {
        $id = Input::get('id');
        $status_id = Input::get('status_id');

        if($id && $status_id)
        {
            $obj = Estimate::find($id);
            $obj->status_id = $status_id;
            $obj->save();

            // Log Acitivity
            $description = sprintf( __('form.act_changed_status_of'), 
                        anchor_link($obj->number, route('show_estimate_page', $obj->id ))) . ' '. __('form.to') . ' '.  $obj->status->name;
            log_activity($obj, $description);

            return response()->json(
                array(
                    'status' => 1,
                    'item_status' => [
                        'name' => $obj->status->name,
                        'id' => $obj->status_id
                    ]
                )
            );
        }
        else
        {
            return response()->json(
                array(
                    'status' => 2
                )
            );
        }
    }

    function customer_view($id)
    {
        if($id)
        {
            $rec = Estimate::find($id);

            $rec->array_of_taxes_used = [];
            if(isset($rec->taxes) && $rec->taxes)
            {
                $rec->array_of_taxes_used = json_decode($rec->taxes);

            }

            $data['html'] = view('estimate.partials.show.estimate', compact('rec'))->render();

            return view('estimate.customer_view', compact('data'))->with('rec', $rec);
        }
    }

    function download_estimate($id, $for_email = NULL)
    {
        if($id)
        {
            $rec = Estimate::find($id);

            if($rec)
            {
                $rec->array_of_taxes_used = [];

                if(isset($rec->taxes) && $rec->taxes)
                {
                    $rec->array_of_taxes_used = json_decode($rec->taxes);

                }


                $data['html'] = view('estimate.partials.show.print', compact('rec'))->render();

                $data['page_title'] = $rec->number;

                $html = view('layouts.print.template', compact('data'))->render();
                

                $pdf = new Pdf();

                if($for_email)
                {
                    return $pdf->get_pdf_file_path($html);
                }

                $file_name = str_replace(" ", "_", trim($data['page_title']));
                $pdf->download($html, $file_name);

            }

            

        }
    }


    public function convert_to_estimate_from_proposal($proposal_id)
    {
        $proposal           = Proposal::find($proposal_id);

        $data               = Estimate::dropdown();

        // Merging Tax Dropdown Information
        $common_sales_jobs = new CommonSalesJobs($proposal);
        
        // Merging Tax Dropdown Information  
        $data['tax_id_list']    = $common_sales_jobs->merge_tax_dropdown_information($data['tax_id_list']);
        $proposal->items = $proposal->item_line()->get();
        $proposal->tag_id = $proposal->tags()->pluck('tag_id')->toArray();        

        $data['customer_id_list'] = [ $proposal->customer->id => $proposal->customer->name] ;

        // Remove Proposal ID to enable create
        $proposal->proposal_id = $proposal->id;
        unset($proposal->id);


        return view('estimate.create', compact('data'))->with('rec',$proposal );

    }


    function send_to_email(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'estimate_id'                   => 'required',
            'customer_id'                   => 'required',
            'email_template'                => 'required',
            'customer_contact_id'           => 'required',
            'email_cc'                      => 'sometimes|nullable|email',


        ]);

        if ($validator->fails()) 
        {
            session()->flash('message', __('form.email_was_not_sent'));
            return  redirect()->back();
        }


        $estimate_id    = Input::get('estimate_id');
        $email          = Input::get('email');
        $email_cc       = Input::get('email_cc');
        $email_template = Input::get('email_template');

        $estimate       = Estimate::find($estimate_id);
        $contact        = CustomerContact::find(Input::get('customer_contact_id'));

        $replacements = [          
            'estimate_number'           => anchor_link($estimate->number, route('estimate_customer_view', [$estimate->id, 
                $estimate->url_slug])),
            'contact_first_name'        => $contact->first_name,
            'contact_last_name'         => $contact->last_name,
            'email_signature'           => config()->get('constants.email_signature'),
           
        ];

        $email_template = short_code_parser($email_template, $replacements);

        $estimate->status_id = ESTIMATE_STATUS_SENT;
        $estimate->save();

    
        $mail = Mail::to($contact->email);

        // Include Email CC
        if($email_cc)
        {
            $mail->cc($email_cc);
        }
        
        if(Input::get('add_attachment'))
        {
            $pdf_file_path = $this->download_estimate($estimate->id , TRUE);        
      

            $mail->send(new SendEstimate(['email_template' => $email_template], $estimate, $pdf_file_path));
        }
        else
        {
            $mail->send(new SendEstimate(['email_template' => $email_template], $estimate ));
        }     

        session()->flash('message', __('form.email_sent'));
        return  redirect()->back();
    }


    function accept_estimate(Request $request, Estimate $estimate)
    {
        if($estimate->status_id != ESTIMATE_STATUS_ACCEPTED)
        {
            // Adding Custom validation rules for validating base64 decoded image in laravel
            Validator::extend('imageable', function ($attribute, $value, $params, $validator) {
                    try {
                        Image::make($value);
                        return true;
                    } catch (\Exception $e) {
                        return false;
                    }
            });


           $validator = Validator::make($request->all(), [
                'first_name'                =>  'required',
                'last_name'                 =>  'required',
                'email'                     =>  'required|email',
                'signature'                 =>  'required|imageable',

                
            ], [
                'signature.imageable'       => __('form.invalid_signature_provided'),
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
            }

            DB::beginTransaction();
            $success = false;

            try {

                $encoded_image  = explode(",", Input::get('signature'))[1];
                $decoded_image  = base64_decode($encoded_image);
                $file_name      = 'public/signature-image/'. uniqid() . '.png';
                Storage::put($file_name, $decoded_image);

                // // Saving Data
                $estimate->status_id                            = ESTIMATE_STATUS_ACCEPTED;
                $estimate->accepted_by_first_name               = $request->first_name;
                $estimate->accepted_by_last_name                = $request->last_name;
                $estimate->accepted_by_email                    = $request->email;
                $estimate->accepted_by_signature                = $file_name;
                $estimate->accepted_date                        = date('Y-m-d H:i:s');
                $estimate->save();


                // Notify The Person who Created
                $user_ids[0] = $estimate->created_by;
                
                if($estimate->assigned_to)
                {
                    $user_ids[1] = $estimate->assigned_to;
                }

                $notifiable_users = User::whereIn('id', $user_ids)->get();

                Notification::send($notifiable_users, new EstimateAccepted($estimate));
                

                DB::commit();
                $success = true;

            } 
            catch (\Exception  $e) {
                $success = false;

                DB::rollback();

            }

            if($success) 
            {
                Session::flash('estimate_flash_message', TRUE); 
                return response()->json(['status' => 1]);
            } 
            else 
            {                
                return response()->json(['status' => 3, 'msg' => __('form.could_not_perform_the_requested_action') ]);
            }

        }

        
    }


    function decline_estimate(Estimate $estimate)
    {

        if($estimate->status_id != ESTIMATE_STATUS_ACCEPTED)
        {

            DB::beginTransaction();
            $success = false;

            try {

                // Change Proposal's Status

                $estimate->status_id = ESTIMATE_STATUS_DECLINED;
                $estimate->save();

                // Notify Users
                $user_ids[0] = $estimate->created_by;
                
                if($estimate->assigned_to)
                {
                    $user_ids[1] = $estimate->assigned_to;
                }

                $notifiable_users = User::whereIn('id', $user_ids)->get();

                Notification::send($notifiable_users, new EstimateDeclined($estimate));

                Session::flash('estimate_flash_message', TRUE); 

                DB::commit();
                $success = true;
                
            }
            catch (\Exception  $e) {

                $success = false;
                DB::rollback();
                echo $e->getMessage();
                die();

            }

             
        }
       


       return  redirect()->back();
    }


    function settings()
    {
        $records    = Setting::whereIn('option_key', ['terms_estimate'])->get();
        $rec        = [];

        if(count($records) > 0)
        {
            $rec            = new \stdClass();
            foreach ($records as $row) 
            {
                $rec->{$row->option_key} = $row->option_value;  
            }


        }       
        
        $data = [];

       return view('estimate.settings_estimate', compact('data'))->with('rec', $rec);
    }

    function update_settings(Request $request)
    {

        $obj = Setting::updateOrCreate(['option_key' => 'terms_estimate' ]);
        $obj->option_value = Input::get('terms_estimate');
        $obj->save();

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();
    }



}
