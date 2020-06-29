<?php

namespace App\Http\Controllers;

use App\Customer;

use App\CreditNoteStatus;

use App\Item;

use App\NumberGenerator;
use App\CreditNote;
use App\CreditNoteItem;
use App\AppliedCredit;
use App\Tax;
use App\CustomerContact;
use App\User;
use App\Setting;
use App\Invoice;
use App\Services\Pdf;
use App\Services\CommonSalesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendCreditNote;
use Carbon\Carbon;


class CreditNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currency_id        = config('constants.default_currency_id');
        
        if($email = get_setting('email_template_credit_note_sent_to_customer'))
        {
            $data['email_template'] = (isset($email->template)) ? $email->template : "";
        }
        else
        {
           $data['email_template']  = "";
        }

        $data['item_statuses'] = [
        	['id' => CREDIT_NOTE_STATUS_OPEN, 'name' => __('form.open')],
        	['id' => CREDIT_NOTE_STATUS_VOID, 'name' => __('form.void')],
        ];

        return view('credit_note.index', compact('data'))->with('rec', []);
    }

    function paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
    
  
        $q                  = CreditNote::query();
        $query              = CreditNote::orderBy('id', 'DESC')->with(['customer']);


        // If the user has permission to view only the ones that are created by himself;
        if(!check_perm('credit_notes_view') && check_perm('credit_notes_view_own'))
        {
            $q->where(function($k){
                $k->where('created_by', auth()->user()->id);
            });
            $query->where(function($k){
                $k->where('created_by', auth()->user()->id);
            });                   
            
        }



        $number_of_records  = $q->get()->count();

        if($search_key)
        {
            $query->where(function ($k) use ($search_key) {
                $k->where('number', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('total', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('tax_total', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('date', 'like', like_search_wildcard_gen(date2sql($search_key)))
        
                    ->orWhere('reference', 'like', like_search_wildcard_gen($search_key))
                    ->orWhereHas('customer', function ($q) use ($search_key) {
                        $q->where('customers.name', 'like', $search_key.'%');
                    })
   
                ;
            });
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();
//

        $rec = [];

        if ($data->count() > 0)
        {
            foreach ($data as $key => $row)
            {

                $rec[] = array(

                    a_links(vue_click_link($row->number, $row->id ) , [
                        
                        [
                            'action_link' => route('edit_credit_note_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission' => 'credt_notes_edit',
                        ],
                       // ['action_link' => route('delete_credit_note', $row->id), 'action_text' => __('form.delete'), 'action_class' => 'delete_item']
                    ]),
                    sql2date($row->date),
                     anchor_link($row->customer->name, route('view_customer_page', $row->customer_id)),
                     $row->status->name,
                     $row->reference,
                    format_currency($row->total, TRUE, $row->get_currency_symbol()),
                    format_currency($row->total - $row->amount_credited, TRUE, $row->get_currency_symbol()),                 
                    

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

        $data = CreditNote::dropdown();
        $rec = new \stdClass();
        $rec->currency_id = config('constants.default_currency_id');
        $rec->terms_and_condition   = get_setting('terms_credit_note');
        return view('credit_note.create', compact('data'))->with('rec', $rec);
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

        ];


        if(!empty($request->items))
        {
            $more_rules = [
                'items.*.description' => 'required',
            ];
            $rules = $rules + $more_rules;

        }

        $validator = Validator::make($request->all(), $rules, $msg);


        if ($validator->fails())
        {
            $errors = $validator->errors();

            $request_all = $request->all();



            if($errors->has('items.*'))
            {
                foreach ($errors->get('items.*') as $key=>$message)
                {
                    preg_match_all('/\d+/', $key , $matches);

                    if(isset($matches[0][0]))
                    {
                        $row_number = $matches[0][0];

                        $item_key = explode('items.'.$row_number.'.', $key)[1];

                        $request_all['items'][$row_number]['validation_error'][$item_key] = implode(",", $message);
                    }


                }
            }



            return  redirect()->back()
                ->withErrors($validator)
                ->withInput($request_all)->with( ['rec' => $request_all , 'data' => $request_all ] );
        }

        DB::beginTransaction();
        $success = false;

        try {

                $request['url_slug']            = md5(microtime());        
                $request['number']              = NumberGenerator::gen(COMPONENT_TYPE_CREDIT_NOTE);
                $request['date']                = date2sql($request->date) ;
                $request['tax_total']           = calculate_tax_total($request->taxes) ;          
                $request['taxes']               = json_encode($request->taxes);              
                $request['created_by']          = auth()->user()->id;
                $request['status_id']			= 1;

                // Create the CreditNote
                $obj = CreditNote::create($request->all());
                
                $common_sales_jobs = new CommonSalesJobs($obj);
                $common_sales_jobs->insert_item_line($request, CreditNoteItem::class ,'credit_note_id');
    


            // Log Activity
            $description = sprintf(__('form.act_created') , __('form.credit_note') );            
            log_activity($obj, $description,  anchor_link($obj->number, route('show_credit_note_page', $obj->id )) );

            DB::commit();
            $success = true;

        } catch (\Exception  $e) {
            $success = false;

            DB::rollback();
           
        }

        if ($success) {
            // the transaction worked ...
            session()->flash('message', __('form.success_add'));
            return redirect()->route('credit_note_list');
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('credit_note_list');
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(CreditNote $credit_note)
    {
        return view('credit_note.show', compact('data'))->with('rec', $credit_note);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    private function is_in_assoc_array($array, $to_look_for, $value)
    {

        if(!empty($array) && is_array($array))
        {
            foreach ($array as $key=>$item)
            {
                if(trim($item[$to_look_for]) == trim($value))
                {
                    return TRUE;
                }
            }

        }

        return FALSE;

    }
    public function edit(CreditNote $credit_note)
    {

        $data = CreditNote::dropdown();

        $common_sales_jobs = new CommonSalesJobs($credit_note);

        // Merging Tax Dropdown Information
        $data['tax_id_list']            = $common_sales_jobs->merge_tax_dropdown_information($data['tax_id_list']);
        $credit_note->items             = $credit_note->item_line()->get(); 
        $data['customer_id_list']       = [ $credit_note->customer->id => $credit_note->customer->name] ;
        return view('credit_note.create', compact('data'))->with('rec', $credit_note );
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

        ];

        $validator = Validator::make($request->all(), $rules, $msg);



        if ($validator->fails())
        {

            return  redirect()->back()
                ->withErrors($validator)
                ->withInput()->with( ['rec' => $request->all() , 'data' => $request->all()] );
        }

        DB::beginTransaction();
        $success = false;

        try {

            $obj = CreditNote::find($id);

            $request['date']                = date2sql($request->date) ;    
            $request['tax_total']           = calculate_tax_total($request->taxes) ;    
            $request['taxes']               = json_encode($request->taxes);                
            

            $obj->update($request->all()) ; 

            // Update Item Line
            $common_sales_jobs = new CommonSalesJobs($obj);
            $common_sales_jobs->update_item_line($request->items, CreditNoteItem::class, 'credit_note_id');                     


            // Log Activity
            $description = sprintf(__('form.act_updated'), __('form.credit_note'));             
            log_activity($obj, $description, anchor_link($obj->number, route('show_credit_note_page', $obj->id )));

            DB::commit();
            $success = true;


        } catch (\Exception  $e) {
            $success = false;

            DB::rollback();

        }

        if ($success) 
        {
            // the transaction worked ...
            session()->flash('message', __('form.success_update'));
            return redirect()->route('credit_note_list');
        } 
        else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('credit_note_list');
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(CreditNote $credit_note)
    {
        DB::beginTransaction();
       
        try {
                // Remove CreditNote Items
                $items = $credit_note->item_line()->get();  

                foreach ($items as $item) 
                {
                   $item->forcedelete();
                }

               

                $credit_note->forcedelete();           

                // Log Acitivity
                $description    = sprintf(__('form.act_deleted'), __('form.credit_note'));                
                log_activity($credit_note, trim($description), $credit_note->number ); 

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

        return redirect()->route('credit_note_list');
    }



    function get_credit_note_details_ajax()
    {
        $id = Input::get('id');

        $rec = CreditNote::find($id);

        $rec->array_of_taxes_used = [];
        
        if(isset($rec->taxes) && $rec->taxes)
        {
            $rec->array_of_taxes_used = json_decode($rec->taxes);

        }

        $rec->hide_status_dropdown              = FALSE;
        $rec->hide_convert_to_button            = FALSE;
        $rec->link_to_converted_component       = "";
        $rec->link_text                         = "";
        $rec->download_url                      = route('download_credit_note', $rec->id);

        $returnHTML = view('credit_note.partials.show.credit_note', compact('rec'))->render();
        return response()->json(
            array(
                'status'        => 1,
                'html'          => $returnHTML,
                'records'       => $rec,
                'item_status'   => [
                    'name'  => $rec->status->name,
                    'id'    => $rec->status_id
                ]
         
            )
        );
    }


    function change_status()
    {
        $id             = Input::get('id');
        $status_id      = Input::get('status_id');

        if($id && $status_id)
        {
            $obj                = CreditNote::find($id);
            $obj->status_id     = $status_id;
            $obj->save();

            // Log Acitivity
            $description = sprintf( __('form.act_has_change_status_of_'), 
                        anchor_link($obj->number, route('show_credit_note_page', $obj->id )),
                        $obj->status->name );
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

    

    function download_credit_note($id, $for_email = NULL)
    {
        if($id)
        {
            $rec = CreditNote::find($id);

            if($rec)  
            
            {
                $rec->array_of_taxes_used = [];

                if(isset($rec->taxes) && $rec->taxes)
                {
                    $rec->array_of_taxes_used = json_decode($rec->taxes);

                }


                $data['html'] = view('credit_note.partials.show.print', compact('rec'))->render();

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


    


    function send_to_email(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'credit_note_id'                   => 'required',
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


        $credit_note_id     = Input::get('credit_note_id');
        $email              = Input::get('email');
        $email_cc           = Input::get('email_cc');
        $email_template     = Input::get('email_template');

        $credit_note        = CreditNote::find($credit_note_id);
        $contact            = CustomerContact::find(Input::get('customer_contact_id'));

        $replacements = [          
            'credit_note_number'           => anchor_link($credit_note->number, route('credit_note_customer_view', [$credit_note->id, 
                $credit_note->url_slug])),
            'contact_first_name'        => $contact->first_name,
            'contact_last_name'         => $contact->last_name,
            'email_signature'           => config()->get('constants.email_signature'),
           
        ];

        $email_template = short_code_parser($email_template, $replacements);

        $credit_note->status_id = ESTIMATE_STATUS_SENT;
        $credit_note->save();

    
        $mail = Mail::to($contact->email);

        // Include Email CC
        if($email_cc)
        {
            $mail->cc($email_cc);
        }
        
        if(Input::get('add_attachment'))
        {
            $pdf_file_path = $this->download_credit_note($credit_note->id , TRUE);        
      

            $mail->send(new SendCreditNote(['email_template' => $email_template], $credit_note, $pdf_file_path));
        }
        else
        {
            $mail->send(new SendCreditNote(['email_template' => $email_template], $credit_note ));
        }     

        session()->flash('message', __('form.email_sent'));
        return  redirect()->back();
    }


    


    function settings()
    {
        $records    = Setting::whereIn('option_key', ['terms_credit_note'])->get();
        $rec        = [];
        
        if($records->count() > 0)
        {
            $rec            = new \stdClass();
            foreach ($records as $row) 
            {
                $rec->{$row->option_key} = $row->option_value;  
            }

        }       
        

       return view('credit_note.settings_credit_note', compact('data'))->with('rec', $rec);
    }

    function update_settings(Request $request)
    {


        $obj = Setting::updateOrCreate(['option_key' => 'terms_credit_note' ]);
        $obj->option_value = Input::get('terms_credit_note');
        $obj->save();

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();
    }


    function get_available_credit_notes_by_customer_id(Request $request)
    {
        $data = CreditNote::select([ 'id' ,'url_slug', 'number', 'date', 'total', 'amount_credited', DB::raw("'' AS invalid") ])->where('customer_id', $request->customer_id)->where('status_id', CREDIT_NOTE_STATUS_OPEN)->get();

        return response()->json($data);
    }


    function apply_credit_to_invoice(Request $request)
    {
        $validator = Validator::make($request->all(), [           
            'invoice_id'                        => 'required',
            'total_amount_to_credit'            => 'required|numeric',
            // 'items.*.amount_to_credit'          => 'required|numeric',


        ]);

        if ($validator->fails()) 
        {           
            return  redirect()->back();            
        }

        DB::beginTransaction();
        $success = false;

        try {

            $invoice = Invoice::find($request->invoice_id);

            if(in_array($invoice->status_id, [INVOICE_STATUS_UNPAID, INVOICE_STATUS_PARTIALLY_PAID, INVOICE_STATUS_OVER_DUE]))
            {
                $amount_allowed_to_credit = $invoice->total - ($invoice->amount_paid + $invoice->applied_credits);

                if($request->total_amount_to_credit <= $amount_allowed_to_credit)
                {
                    if($amount_allowed_to_credit == $request->total_amount_to_credit)
                    {
                        $invoice->status_id = INVOICE_STATUS_PAID;
                    }
                    else
                    {
                        $invoice->status_id = INVOICE_STATUS_PARTIALLY_PAID;
                    }

                    $invoice->applied_credits = $invoice->applied_credits + $request->total_amount_to_credit;
                    $invoice->save();

                  
                    foreach ($request->items as $credit_note_id=>$row) 
                    {
                        if($row['amount_to_credit'] > 0)
                        {
                            $credit_note = CreditNote::find($credit_note_id);

                            if($credit_note->count() > 0)                             
                            {
                                if($credit_note->status_id == CREDIT_NOTE_STATUS_OPEN)
                                {
                                    $creditable_amount = $credit_note->total - $credit_note->amount_credited;
                                    
                                    if($row['amount_to_credit'] <= $creditable_amount)
                                    {
                                        $credit_note->amount_credited = $credit_note->amount_credited + $row['amount_to_credit'];

                                        if($row['amount_to_credit'] == $creditable_amount)
                                        {
                                            $credit_note->status_id = CREDIT_NOTE_STATUS_ADJUSTED;
                                        }

                                        $credit_note->save();

                                        $applied_credit = new AppliedCredit();

                                        $applied_credit->credit_note_id = $credit_note->id;
                                        $applied_credit->invoice_id     = $invoice->id;
                                        $applied_credit->date           = date("Y-m-d");
                                        $applied_credit->amount         = $row['amount_to_credit'];
                                        $applied_credit->created_by     = auth()->user()->id;
                                        $applied_credit->save();
                                        
                                    }
                                }
                            }
                        }
                    }

                }
                
            }
            
   


            DB::commit();
            $success = true;
        } catch (\Exception  $e) {
            
            $success = false;
            DB::rollback();

        }

        if ($success) {
            // the transaction worked ...
            session()->flash('message', __('form.success_applied'));
            return  redirect()->back();
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->back();
        }

        
    }
   

   function get_invoices_applied_to(Request $request)
   {
        $credit_note = CreditNote::with('applied_to_invoices')->find($request->id);

        if($credit_note->count() > 0)        
        {
            $currency_symbol = $credit_note->currency->symbol;

            if(isset($credit_note->applied_to_invoices) && !empty($credit_note->applied_to_invoices))
            {
            
                foreach ($credit_note->applied_to_invoices as $row) 
                {
                    $data[] = [ 
                        'invoice_number' => $row->invoice->number, 
                        'invoice_url' => route('show_invoice_page', $row->invoice->id), 
                        'date' => sql2date($row->date), 
                        'amount' => format_currency($row->amount, TRUE, $currency_symbol) 
                    ];
                }

                return response()->json($data);
            }
        }
   }

}
