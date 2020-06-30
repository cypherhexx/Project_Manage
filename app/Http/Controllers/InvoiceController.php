<?php

namespace App\Http\Controllers;

use App\PaymentApiResponse;
use App\Estimate;
use App\Expense;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceStatus;
use App\NumberGenerator;
use App\TimeSheet;
use App\Payment;
use App\PaymentMode;
use App\Proposal;
use App\Currency;
use App\Customer;
use App\Services\CommonSalesJobs;
use App\Services\Pdf;
use App\CustomerContact;
use App\Project;
use App\User;
use App\Setting;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendInvoice;
use App\Services\PaymentGateway\Contracts\PaymentBean;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Notifications\PaymentReceived;
use App\Task;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Payout;
use PayPal\Api\PayoutSenderBatchHeader;
use PayPal\Api\PayoutItem;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment as Payment1;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;

use PayPal\Api\ChargeModel;
use PayPal\Api\Currency as Currency1;   
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;

// use to process billing agreements
use PayPal\Api\Agreement;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\ShippingAddress;

use Srmklive\PayPal\Services\ExpressCheckout;



class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    protected static  $provider;
    private $apiContext;
    private $mode;
    private $client_id;
    private $secret;

    
    public function __construct()
    {
       
       self::$provider = new ExpressCheckout;  
        if(config('paypal.settings.mode') == 'live'){
            $this->client_id = config('paypal.live_client_id');
            $this->secret = config('paypal.live_secret');
        } else {
            $this->client_id = config('paypal.sandbox_client_id');
            $this->secret = config('paypal.sandbox_secret');
        }
        
        // Set the Paypal API Context/Credentials
        $this->apiContext = new ApiContext(new OAuthTokenCredential($this->client_id, $this->secret));
        $this->apiContext->setConfig(config('paypal.settings'));
    }


    public function index(Request $request)
    {

       $rec['payment_mode_id_list']        = PaymentMode::whereNull('inactive')
                                                ->whereNULL('inactive')->orderBy('name','ASC')                     
                                                ->select('name AS text', 'id')->get();

       $rec['item_statuses']               = InvoiceStatus::all();  

       $data                                = Invoice::stat(config('constants.default_currency_id'));

       $data['invoice_statuses_id_list']   = InvoiceStatus::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        $data['default_invoice_status_id_list'] = [
            INVOICE_STATUS_UNPAID, 
            INVOICE_STATUS_PARTIALLY_PAID, 
            INVOICE_STATUS_OVER_DUE,
            INVOICE_STATUS_DRAFT,
        ];

        if($email = get_setting('email_template_invoice_sent_to_customer'))
        {
            $data['email_template'] = (isset($email->template)) ? $email->template : "";
        }
        else
        {
           $data['email_template']  = "";
        }

        $data['recurring_invoice_type'] = [
            ['text' => __('form.no'),  'id' => 0 ],
            ['text' => __('form.every_one_month'), 'id' => 1],
            ['text' => __('form.every_two_month'), 'id' => 2],
            ['text' => __('form.every_three_month'), 'id' => 3],
            ['text' => __('form.every_four_month'), 'id' => 4],
            ['text' => __('form.every_five_month'), 'id' => 5],
            ['text' => __('form.every_six_month'), 'id' => 6],
            ['text' => __('form.every_seven_month'), 'id' => 7],
            ['text' => __('form.every_eight_month'), 'id' => 8],
            ['text' => __('form.every_nine_month'), 'id' => 9],
            ['text' => __('form.every_ten_month'), 'id' => 10],
            ['text' => __('form.every_eleven_month'), 'id' => 11],
            ['text' => __('form.every_twelve_month'), 'id' => 12],
            ['text' => __('form.custom'), 'id' => 'custom']
        ];

        $data['recurring_invoice_custom_type_list'] = [
            ['text' => __('form.day_s'),  'id' => 'days' ],
            ['text' => __('form.week_s'), 'id' => 'weeks'],
            ['text' => __('form.month_s'), 'id' => 'months'],
            ['text' => __('form.year_s'), 'id' => 'years'],
   
        ];

        return view('invoice.index', compact('data'))->with('rec', $rec);
    }

    function paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $customer_id        = Input::get('customer_id');
        $project_id         = Input::get('project_id');
        $status_id          = Input::get('status_id');

        $q                  = Invoice::query();
        $query              = Invoice::orderBy('id', 'DESC')->with(['status', 'tags', 'customer', 'project']);

        // If the user has permission to view only the ones that are created by himself;
        if(!check_perm('invoices_view') && check_perm('invoices_view_own'))
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
        if($project_id)
        {
            $q->where('project_id', $project_id);
            $query->where('project_id', $project_id);
        }

        // Data Filtering
        if($status_id)
        {
            $query->whereIn('status_id', $status_id);
        }

        // End of data Filtering

        $number_of_records  = $q->get()->count();

        if ($search_key)
        {
            $query->where(function ($k) use ($search_key) {
                $k->where('number', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('total', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('tax_total', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('date', 'like', like_search_wildcard_gen(date2sql($search_key)))
                    ->orWhere('due_date', 'like', like_search_wildcard_gen(date2sql($search_key)))
                   ->orWhere('reference', 'like', like_search_wildcard_gen($search_key))
                    ->orWhereHas('customer', function ($q) use ($search_key) {
                        $q->where('customers.name', 'like', $search_key . '%');
                    })
                     ->orWhereHas('project', function ($q) use ($search_key) {
                        $q->where('projects.name', 'like', $search_key . '%');
                    })
                    ->orWhereHas('tags', function ($q) use ($search_key) {
                        $q->where('name', 'like', $search_key . '%');
                    })
                    ->orWhereHas('status', function ($q) use ($search_key) {
                        $q->where('name', 'like', $search_key . '%');
                });
            });
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();
//

        $rec = [];

        if (count($data) > 0) {
            foreach ($data as $key => $row) 
            {

                $act = [];
                array_push($act,  [
                    'action_link' => route('invoice_customer_view', [$row->id, $row->url_slug]), 
                    'action_text' => __('form.view'), 'action_class' => '', 'new_tab' => TRUE,
                    'permission' => 'invoices_view',
                ]);

                // 
                if(!in_array($row->status_id, [ INVOICE_STATUS_PARTIALLY_PAID , INVOICE_STATUS_PAID ]))
                {
                    array_push($act, [
                        'action_link' => route('edit_invoice_page', $row->id), 
                        'action_text' => __('form.edit'), 'action_class' => '',
                        'permission' => 'invoices_edit',
                    ]);
                }
                

                $rec[] = array(

                    a_links(vue_click_link($row->number, $row->id, route('show_invoice_page', $row->id)), $act),
                    format_currency($row->total, TRUE, $row->get_currency_symbol()),
                    format_currency($row->tax_total, TRUE, $row->get_currency_symbol()) ,
                    isset(($row->date)) ? sql2date($row->date) : "",
                    anchor_link($row->customer->name, route('view_customer_page', $row->customer_id )),
                                 
                    isset($row->project->name) ? anchor_link($row->project->name, route('show_project_page', $row->project->id)) : "",
                    $row->get_tags_as_badges(true),    
                    isset(($row->due_date)) ? sql2date($row->due_date) : "",

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
    public function create(Request $request)
    {

        $data = Invoice::dropdown();
        $rec = new \stdClass();
        $rec->currency_id           = config('constants.default_currency_id');
        $rec->terms_and_condition   = get_setting('terms_invoice');


        if ($request->session()->has('invoicing_for_project')) 
        {
            $invoicing_for_project  = session('invoicing_for_project');
            $data                   = $invoicing_for_project['data'];
            $rec                    = $invoicing_for_project['rec'];
            $request->session()->forget('invoicing_for_project');

        }
        return view('invoice.create', compact('data'))->with('rec', $rec);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $rules = [
            'customer_id'   => 'required',
            'currency_id'   => 'required',
            'date'          => 'required',
        ];
        $msg = [
            'customer_id.required'              => sprintf(__('form.field_is_required'), __('form.customer')),
            'currency_id.required'              => sprintf(__('form.field_is_required'), __('form.currency')),
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

            $invoice_created_from = "";


          
            $request['url_slug']    = md5(microtime());
            $request['number']      = NumberGenerator::gen(COMPONENT_TYPE_INVOICE);
            $request['status_id']   = (is_countable($request->items) && count($request->items) > 0) ? INVOICE_STATUS_UNPAID : INVOICE_STATUS_DRAFT;
            $request['date']        = date2sql($request->date);
            $request['due_date']    = ($request->due_date) ? date2sql($request->due_date) : NULL;
            $request['tax_total']   = calculate_tax_total($request->taxes);
            $request['taxes']       = json_encode($request->taxes);
            
            
            
            $request['created_by']  =  auth()->user()->id;


            // Create the Invoice
            $obj = Invoice::create($request->all());
   
            $common_sales_jobs = new CommonSalesJobs($obj);
            $common_sales_jobs->insert_item_line($request, InvoiceItem::class ,'invoice_id');

            $obj->tag_attach($request->tag_id);

            

            // Change Proposal Status if it was converted to Invoice
            if(isset($request->proposal_id) && $request->proposal_id)
            {
                $proposal = Proposal::find($request->proposal_id);
                $proposal->converted_to = COMPONENT_TYPE_INVOICE;
                $proposal->converted_to_id = $obj->id;
                $proposal->status_id = PROPOSAL_STATUS_ACCEPTED;
                $proposal->save();
                $invoice_created_from = sprintf(__('form.act_from_'), anchor_link($proposal->number, route('show_proposal_page', $proposal->id ) ));
            }


            // Change Estimate Status if it was converted to Invoice
            if(isset($request->estimate_id) && $request->estimate_id)
            {
                $estimate               = Estimate::find($request->estimate_id);
                $estimate->invoice_id   = $obj->id;
                $estimate->status_id    = ESTIMATE_STATUS_ACCEPTED;
                $estimate->save();
                $invoice_created_from = sprintf(__('form.act_from_'), anchor_link($estimate->number, route('show_estimate_page', $estimate->id ) ));
            }

            // Update Expense if it was converted to Invoice
            if(isset($request->expense_id) && $request->expense_id)
            {
                $expense               = Expense::find($request->expense_id);
                $expense->invoice_id   = $obj->id;
                $expense->save();
                $invoice_created_from = sprintf(__('form.act_from_'), anchor_link($expense->number, route('edit_expense_page', $expense->id ) ));
            }


             // Log Activity
            if($obj->project_id)
            {
                $project        = Project::find($obj->project_id);                   
                $log_name       = LOG_NAME_PROJECT . $project->id;
            }
            else
            {
                $log_name       = LOG_NAME_DEFAULT;
            }

            $description = sprintf(__('form.act_created'), __('form.invoice'));

            $value_to_store = anchor_link($obj->number, route('show_invoice_page', $obj->id )) . " ".$invoice_created_from;
            log_activity($obj, trim($description), $value_to_store, $log_name);


            DB::commit();
            $success = true;
        } catch (\Exception  $e) {
            $success = false;

            DB::rollback();
            
        }

        if ($success) {
            // the transaction worked ...
            session()->flash('message', __('form.success_add'));
            return redirect()->route('show_invoice_page', $obj->id);
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->back();
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Invoice $invoice)
    {
        return view('invoice.show', compact('data'))->with('rec', $invoice);

    }

   


    public function edit(Invoice $invoice)
    {
        $response = $this->get_records_for_edit_option($invoice);

        $data = $response['data'];

        $rec = $response['rec'];

        return view('invoice.create', compact('data'))->with('rec', $rec);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {


        $rules = [
            'customer_id'   => 'required',
            'currency_id'   => 'required',
            'date'          => 'required',
        ];
        $msg = [
            'customer_id.required'              => sprintf(__('form.field_is_required'), __('form.customer')),
            'currency_id.required'              => sprintf(__('form.field_is_required'), __('form.currency')),
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

            $obj = Invoice::find($id);

            $request['date']                = date2sql($request->date) ;
            $request['due_date']            = ($request->due_date) ? date2sql($request->due_date) : NULL ;
            $request['tax_total']           = calculate_tax_total($request->taxes);
            $request['taxes']               = (!empty($request->taxes)) ? json_encode($request->taxes) : NULL;
            

            // Update Invoice
            $obj->update($request->all()) ;

            // 
            // Update Item Line
            $common_sales_jobs = new CommonSalesJobs($obj);
            $common_sales_jobs->update_item_line($request->items, InvoiceItem::class, 'invoice_id');
            
            $obj->tag_sync($request->tag_id);               

             // Log Activity
            $description = __('form.act_has_updated_invoice'). anchor_link($obj->number, route('show_invoice_page', $obj->id )) ;
            log_activity($obj, trim($description));


            DB::commit();
            $success = true;
        } catch (\Exception  $e) {
            $success = false;

            DB::rollback();
            
        }

        if ($success) {
            // the transaction worked ...
            session()->flash('message', __('form.success_update'));
            return redirect()->route('invoice_list');
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('invoice_list');
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        DB::beginTransaction();
       
        try {
                // Remove Estimate Items
                $items = $invoice->item_line()->get();  

                foreach ($items as $item) 
                {
                   $item->forcedelete();
                }

                 // Remove Tags
                $invoice->tag_sync([]);
             

                $invoice->forcedelete();           

                // Log Acitivity
                $description    = sprintf(__('form.act_deleted'), __('form.invoice'));                
                log_activity($invoice, trim($description), $invoice->number ); 

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

        return redirect()->route('invoice_list');
    }


    function get_invoice_details_ajax()
    {
        $id = Input::get('id');

        $rec = Invoice::with('payments')->find($id);

        $rec->array_of_taxes_used = [];

        if (isset($rec->taxes) && $rec->taxes) 
        {
            $rec->array_of_taxes_used = json_decode($rec->taxes);
        }

        $amount_due = $rec->total - ( $rec->amount_paid +  $rec->applied_credits);

        $returnHTML = view('invoice.partials.show.invoice', compact('rec'))->render();

        return response()->json(
            array(
                'status' => 1,
                'html' => $returnHTML,
                'item_status' => [
                    'name' => $rec->status->name,
                    'id' => $rec->status_id
                ],
                'url_to_invoice_customer_view' => route('invoice_customer_view', [$id, $rec->url_slug ]),
                'records' => [
                    'amount_due'                                    => $amount_due, 
                    'amount_due_formatted'                          => format_currency($amount_due), 
                    'customer_id'                                   => $rec->customer_id,
           
                    'recurring_invoice_type'                        => $rec->recurring_invoice_type,
                    'recurring_invoice_total_cycle'                 => $rec->recurring_invoice_total_cycle,
                    'is_recurring_invoice_period_infinity'          => $rec->is_recurring_invoice_period_infinity,
                    'recurring_invoice_custom_parameter'            => $rec->recurring_invoice_custom_parameter,
                    'recurring_invoice_custom_type'                 => $rec->recurring_invoice_custom_type,
                    'customer_available_credits'                    => $rec->customer->available_credits()->amount,
                    'invoice_number'                                => $rec->number,
                    

                ]
            )
        );
    }


    function change_status()
    {
        $id = Input::get('id');
        $status_id = Input::get('status_id');

        if ($id && $status_id) 
        {
            $obj = Invoice::find($id);
            $obj->status_id = $status_id;
            $obj->save();

            // Log Acitivity
            $description = sprintf( __('form.act_changed_status_of'), 
                        anchor_link($obj->number, route('show_invoice_page', $obj->id ))) . ' '. __('form.to') . ' '.  $obj->status->name;
                        

            log_activity($obj, $description);


            return response()->json(['status' => 1,
                    'item_status' => [ 'name' => $obj->status->name, 'id' => $obj->status_id ]
               ]);               
            
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

        if ($id) 
        {
            $invoice = Invoice::with('payments')->find($id);

            $invoice->array_of_taxes_used = [];

            if (isset($invoice->taxes) && $invoice->taxes) 
            {
                $invoice->array_of_taxes_used = json_decode($invoice->taxes);
            }

            $data['html']                   = view('invoice.partials.show.invoice')->with('rec' , $invoice)->render();  
            $invoice->amount_due            =  calculate_invoice_amount_due($invoice->total, $invoice->amount_paid, $invoice->applied_credits);
            $currency                       = $invoice->get_currency();
            $data['currency_symbol']        = $currency['symbol'];
            $invoice->currency_code         =  strtolower($currency['iso']) ;           
            $data['online_payment_modes']   = PaymentMode::get_online_payment_gateways_dropdown_for_payment($invoice, $invoice->currency_code);

            return view('invoice.customer_view', compact('data'))->with('rec', $invoice);
        }
    }

    function download_invoice($id, $for_email = NULL)
    {
      
        if ($id) 
        {
            $rec = Invoice::with('payments')->find($id);

            if($rec)
            {
                
                $rec->array_of_taxes_used = [];

                if (isset($rec->taxes) && $rec->taxes) 
                {
                    $rec->array_of_taxes_used = json_decode($rec->taxes);
                }

                $data['html'] = view('invoice.partials.show.print', compact('rec'))->render();

                $data['page_title'] = $rec->number;

                $html   = view('layouts.print.template', compact('data'))->with('rec', $rec)->render();

                $pdf    = new Pdf();

                if($for_email)
                {
                    return $pdf->get_pdf_file_path($html);
                }

                $file_name = str_replace(" ", "_", trim($data['page_title']));
                
                
                $pdf->download($html, $file_name);

            }
            else
            {
                abort(404);
            }
        }
        else
        {
            abort(404);
        }
    }

    
    function receive_payment(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'invoice_id'        => 'required',
            'amount'            => 'required',
            'date'              => 'required',
            'payment_mode_id'   => 'required',
        ]);


        if ($validator->fails())
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->back()->withErrors($validator);
        }

        DB::beginTransaction();
        $success = false;

        try {

            $invoice = Invoice::find($request->invoice_id);

            if($invoice)
            {
                $amount_paid_value_before_entry = $invoice->amount_paid + $invoice->applied_credits ;  
                $total_received_amount_would_be = $amount_paid_value_before_entry + Input::get('amount');             

                $validator = Validator::make(['amount' =>  $total_received_amount_would_be ], [

                    'amount' => [function ($attribute, $value, $fail) use($invoice) {
                        if ($value > $invoice->total) {
                            $fail(__('form.over_received_amount'));
                        }
                    }]

                ]);


                if ($validator->fails())
                {
                    session()->flash('message', __('form.could_not_perform_the_requested_action'));
                    return redirect()->back()->withErrors($validator);
                }

                $paymentBean = new PaymentBean();

                $paymentBean->date($request->date)
                            ->amount($request->amount)
                            ->payment_mode_id($request->payment_mode_id)
                            ->reference($request->transaction_id)
                            ->note($request->note);

                $payment = $invoice->payment_received($paymentBean);                


                // Log Activity
                if($invoice->project_id)
                {
                    $project        = Project::find($invoice->project_id);                   
                    $log_name       = LOG_NAME_PROJECT . $project->id;
                }
                else
                {
                    $log_name       = LOG_NAME_DEFAULT;
                }

                $description = __('form.act_received_payment') ;

                $value_to_store = anchor_link($payment->number, route('show_payment_page', $payment->id )) ;
                log_activity($payment, trim($description), $value_to_store, $log_name);


            }

            DB::commit();
            $success = true;

        } catch (\Exception  $e) {
            $success = false;
            
            DB::rollback();
           
        }
       
        if ($success)
        {
            // the transaction worked ...
            session()->flash('message', __('form.success_record_payment'));

        }
        else
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));

        }

        return redirect()->back();
    }

    function get_invoice_payments()
    {
        $id = Input::get('id');

        if($id)
        {
            $payments = Payment::with(['payment_mode'])->where('invoice_id', $id)->get();

            

            if(count($payments) > 0)
            {                

                return response()->json(['status' => 1, 'data' => $payments ]);
            }
            else
            {
                return response()->json(['status' => 2, 'data' => __('form.no_payments_received_yet') ]);
            }
        }



    }



    public function get_records_for_edit_option($invoice)
    {

        $data = Invoice::dropdown();
    
        $common_sales_jobs = new CommonSalesJobs($invoice);
        
        // Merging Tax Dropdown Information  
        $data['tax_id_list']    = $common_sales_jobs->merge_tax_dropdown_information($data['tax_id_list']);
        $invoice->items = $invoice->item_line()->get();
        $invoice->tag_id = $invoice->tags()->pluck('tag_id')->toArray();

        

        $data['customer_id_list']   = [$invoice->customer->id => $invoice->customer->name];

        if($invoice->project_id)
        {
          $data['project_id_list']    = [$invoice->project->id => $invoice->project->name];  
        }        

        return ['data' => $data, 'rec' => $invoice];
    }


    function convert_to_invoice_from_proposal($id)
    {
        $proposal = Proposal::find($id);

        $response = $this->get_records_for_edit_option($proposal);

        $data = $response['data'];

        $rec = $response['rec'];

        // Remove the Proposal ID to enable create option
        $rec->proposal_id = $rec->id;
        unset($rec->id);
        return view('invoice.create', compact('data'))->with('rec', $rec);

    }

    function convert_to_invoice_from_estimate($id)
    {
        $estimate = Estimate::find($id);

        $response = $this->get_records_for_edit_option($estimate);

        $data = $response['data'];

        $rec = $response['rec'];

        // Remove the Proposal ID to enable create option
        $rec->estimate_id = $rec->id;
        unset($rec->id);
        return view('invoice.create', compact('data'))->with('rec', $rec);

    }



    function convert_to_invoice_from_expense($id)
    {
        $rec    = Expense::with(['category', 'customer', 'project'])->find($id);

        // If a customer is involved and no invoice has been created
        if($rec && $rec->customer_id && (!$rec->invoice_id))
        {
            $data   = Invoice::dropdown();


            if($rec->customer)
            {
                $data['customer_id_list'] = [$rec->customer->id => $rec->customer->name];

                $rec->address               = $rec->customer->address;
                $rec->city                  = $rec->customer->city;
                $rec->state                 = $rec->customer->state;
                $rec->zip_code              = $rec->customer->zip_code;
                $rec->country_id            = $rec->customer->country_id;

                $rec->shipping_address      = $rec->customer->shipping_address;
                $rec->shipping_city         = $rec->customer->shipping_city;
                $rec->shipping_state        = $rec->customer->shipping_state;
                $rec->shipping_zip_code     = $rec->customer->shipping_zip_code;
                $rec->shipping_country_id   = $rec->customer->shipping_country_id;

            }


            /* Merge Tax List. The reason behind merging the tax array is, in case any tax was edited or deleted
              we can still get the recorded tax in the dropdown select option.*/
            if($rec->tax_id)
            {
                $taxes = json_decode($rec->tax_id);


                if(is_array($taxes) && count($taxes) > 0)
                {
                    foreach ($taxes as $key => $display_as)
                    {

                        if(in_assoc_array($data['tax_id_list'] , 'id', $display_as) != TRUE)
                        {
                            $parsed_tax_string = parse_tax_string($display_as);

                            $rate = $parsed_tax_string['rate'] ;
                            $name = $parsed_tax_string['name'] . SEPARATOR_TAX_NAME_RATE. $rate ;
                            $data['tax_id_list'][$key] = [
                                'id'    => $display_as ,
                                'name'  => $name,
                                'text'  => $name,
                                'rate'  => $rate
                            ];
                        }


                    }
                }

            }

            $rec->items = [
                [
                    'description'       => $rec->name,
                    'long_description'  => $rec->note,
                    'quantity'          => 1,
                    'unit'              => '',
                    'rate'              => $rec->amount,
                    'tax_id'            => $rec->tax_id,
                ]
            ];
            // Remove the Expense ID to enable create option
            $rec->expense_id = $rec->id;
            unset($rec->id);

            if($rec->project_id)
            {
              $data['project_id_list']    = [$rec->project->id => $rec->project->name];  
            }

            
            $rec->terms_and_condition   = get_setting('terms_invoice');

            return view('invoice.create', compact('data'))->with('rec', $rec);
        }
        else
        {
            return abort(404);
        }


    }

    function get_unbilled_timesheets_and_expenses_by_customer_id()
    {
       $rec = [];

        // Get the billable time sheet of the customer
         $sql = 'SELECT time_sheets.*, tasks.title, hourly_rate FROM time_sheets 
                LEFT JOIN tasks ON time_sheets.task_id = tasks.id
                WHERE (task_id IN (
                    SELECT tasks.id FROM tasks
                    INNER JOIN projects ON tasks.component_number = projects.id
                    WHERE customer_id = ? AND billing_type_id = ? AND is_billable = ? AND invoice_id IS NULL
                    AND (hourly_rate IS NOT NULL OR hourly_rate <> "")

                ))
                OR (component_id = ? AND component_number = ? AND  is_billable = ? AND invoice_id IS NULL)
                ';
                
                

        $records = DB::select($sql, [ Input::get('customer_id') , BILLING_TYPE_TASK_HOURS, TRUE,  
            COMPONENT_TYPE_CUSTOMER, Input::get('customer_id') , TRUE ]);  

        if(count($records) > 0)      
        {
            foreach ($records as $row) 
            {
                $start_time = Carbon::createFromFormat('Y-m-d H:i:s' , $row->start_time);
                $end_time   = Carbon::createFromFormat('Y-m-d H:i:s' , $row->end_time);
                //$duration   = $end_time->diff($start_time)->format('%H:%I');
                $quantity   = time_to_decimal($row->duration);

                $rec [] = [
                    'description'               => $row->title,
                    'long_description'          => "",
                    'url_to_source'             => route('show_task_page', $row->task_id),
                    'rate'                      => $row->hourly_rate,
                    'formatted_rate'            => format_currency($row->hourly_rate),
                    'quantity'                  => $quantity,
                    'sub_total'                 => round($row->hourly_rate * $quantity),
                    'formatted_sub_total'       => remove_commas(format_currency($row->hourly_rate * $quantity)),
                    'unit'                      => __('form.hour'),
                    'component_number'          => $row->id,
                    'component_id'              => COMPONENT_TYPE_TIMESHEET,
                ];
            }

            
        }

        // Get Billable Expenses of the Customer
        $customer = Customer::find(Input::get('customer_id'));

        if($customer)
        {
            $expenses = $customer->billable_expenses()->get();
       
            if(count($expenses) > 0)
            {
                foreach ($expenses as $expense) 
                {
                   $rec [] = [
                            'description'               => $expense->name.' ['. __('form.expense')." : " .$expense->category->name .']',
                            'long_description'          => $expense->note,
                            'url_to_source'             => route('expense_list').'?id='.$expense->id,
                            'rate'                      => $expense->amount_after_tax,
                            'formatted_rate'            => format_currency($expense->amount_after_tax),
                            'quantity'                  => 1,
                            'sub_total'                 => round($expense->amount_after_tax),
                            'formatted_sub_total'       => format_currency($expense->amount_after_tax),
                            'unit'                      => '',
                            'component_number'          => $expense->id,
                            'component_id'              => COMPONENT_TYPE_EXPENSE,
                        ];
                }
            }
           
        }

        if(count($rec) > 0)
        {
            return response()->json(['status' => 1 , 'data' => $rec]);    
        }        
        else
        {
            return response()->json(['status' => 2 , 'data' => [] ]);
        }

        
    }



    function send_to_email(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'invoice_id'                    => 'required',
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


        $invoice_id     = Input::get('invoice_id');
        $email          = Input::get('email');
        $email_cc       = Input::get('email_cc');
        $email_template = Input::get('email_template');

        $invoice        = Invoice::find($invoice_id);
        $contact        = CustomerContact::find(Input::get('customer_contact_id'));

        $replacements   = [          
            'invoice_number'        => anchor_link($invoice->number, route('invoice_customer_view', [$invoice->id, 
                $invoice->url_slug])),
            'invoice_status'        => $invoice->status->name,
            'contact_last_name'     => $contact->first_name,
            'contact_lastname'      => $contact->last_name,
            'email_signature'       => config()->get('constants.email_signature'),
            'invoice_link'          => route('invoice_customer_view', [$invoice->id, $invoice->url_slug])
                
           
        ];

        $email_template = short_code_parser($email_template, $replacements);

    
        $mail = Mail::to($contact->email);

        // Include Email CC
        if($email_cc)
        {
            $mail->cc($email_cc);
        }

        if(Input::get('add_attachment'))
        {
            $pdf_file_path = $this->download_invoice($invoice_id , TRUE);        
            $mail->send(new SendInvoice(['email_template' => $email_template], $invoice, $pdf_file_path ));
        }
        else
        {
            $mail->send(new SendInvoice(['email_template' => $email_template], $invoice ));   
        }
        
        

        

        session()->flash('message', __('form.email_sent'));
        return  redirect()->back();
    }



    function report_paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $status_ids         = Input::get('status_ids');
        $sales_agent_ids    = Input::get('sales_agent_ids');
        $date_range         = Input::get('date_range');
        $currency_id        = Input::get('currency_id');


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
        
        $common_query        = Invoice::where('status_id', '<>', INVOICE_STATUS_CANCELED)
                                ->Where('status_id', '<>', INVOICE_STATUS_DRAFT);


        $q                  =  $common_query;
        $query              =  $common_query->with(['status', 'customer']);
                                
        if($currency_id)
        {
            $q->where('currency_id', $currency_id);
            $query->where('currency_id', $currency_id);
        }
        if($status_ids)
        {
            $q->whereIn('status_id', $status_ids);
            $query->whereIn('status_id', $status_ids);
        }
        if($sales_agent_ids)
        {
            $q->whereIn('sales_agent_id', $sales_agent_ids);
            $query->whereIn('sales_agent_id', $sales_agent_ids);
        }

        if($date_from && $date_to)
        {
            $q->whereBetween('date', [$date_from, $date_to ]);
            $query->whereBetween('date', [$date_from, $date_to ]);
        }

        $number_of_records  = $q->get()->count();

        if ($search_key)
        {
//             $query->orwhere('number', 'like', like_search_wildcard_gen($search_key))
//                 ->orWhere('total', 'like', like_search_wildcard_gen($search_key))
//                 ->orWhere('tax_total', 'like', like_search_wildcard_gen($search_key))
//                 ->orWhere('date', 'like', like_search_wildcard_gen(date2sql($search_key)))
//                 ->orWhere('due_date', 'like', like_search_wildcard_gen(date2sql($search_key)))
// //                ->orWhere('reference', 'like', like_search_wildcard_gen($search_key))
//                 ->orWhereHas('customer', function ($q) use ($search_key) {
//                     $q->where('customers.name', 'like', $search_key . '%');
//                 })
               
//                 ->orWhereHas('status', function ($q) use ($search_key) {
//                     $q->where('name', 'like', $search_key . '%');
//                 });
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();
//

        $rec = [];

        if (count($data) > 0) 
        {
            $total              = 0;
            $tax_total          = 0;
            $discount_total     = 0;
            $adjustment         = 0;
            $applied_credits    = 0;
            $open_amount        = 0;

            $currency                   = Currency::find($currency_id);
            $currency_symbol            = ($currency) ? $currency->symbol : NULL ;

            foreach ($data as $key => $row) 
            {
          

                $rec[] = array(                   
                       
                    anchor_link( $row->number, route('show_invoice_page', $row->id)),
                    anchor_link($row->customer->name, route('view_customer_page', $row->customer_id )),
                    isset(($row->date)) ? sql2date($row->date) : "",
                    isset(($row->due_date)) ? sql2date($row->due_date) : "",
                    format_currency($row->total, true, $currency_symbol  ),
                    format_currency($row->tax_total, true , $currency_symbol ),                    
                    format_currency($row->discount_total, true , $currency_symbol ),                
                    format_currency($row->adjustment, true , $currency_symbol ),    
                    format_currency($row->applied_credits, true , $currency_symbol ), 
                    format_currency($row->total - ($row->amount_paid + $row->applied_credits), true , $currency_symbol  ), 
                    $row->status->name,

                );

                $total              += $row->total;
                $tax_total          += $row->tax_total;
                $discount_total     += $row->discount_total;
                $adjustment         += $row->adjustment;
                $applied_credits    += $row->applied_credits;
                $open_amount        += $row->total - ($row->amount_paid + $row->applied_credits);
                
               

            }

            array_push($rec, [

                '<b>'. __('form.total_per_page'). '<b>',
                "",
                "",
                "",
                '<b>'. format_currency($total, true , $currency_symbol  ). '<b>',
                '<b>'.format_currency($tax_total, true , $currency_symbol ) . '<b>',                    
                '<b>'.format_currency($discount_total, true , $currency_symbol ) . '<b>',                
                '<b>'.format_currency($adjustment, true , $currency_symbol ). '<b>',    
                '<b>'.format_currency($applied_credits, true , $currency_symbol ). '<b>', 
                '<b>'.format_currency($open_amount, true , $currency_symbol ). '<b>', 
                '',

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


    function report_item_paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $currency_id        = Input::get('currency_id');
        $sales_agent_ids    = Input::get('sales_agent_ids');
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
     

        
        $common_query       = InvoiceItem::whereHas('invoice', function($qu) use($sales_agent_ids, $date_from, $date_to, $currency_id) {
                                $qu->where('status_id', '=', INVOICE_STATUS_PAID);

                                if($sales_agent_ids)
                                {
                                        $qu->whereIn('sales_agent_id', $sales_agent_ids);                                     
                                }
                                if($date_from && $date_to)
                                {                       
                                    $qu->whereBetween('date', [$date_from, $date_to ]);
                                }

                                if($currency_id)
                                {
                                    $qu->where('currency_id', $currency_id);
                                    
                                }

                            })->groupBy('description');



        $q                  = $common_query;
                            

        $query              = $common_query
                                ->select('invoice_items.*', 
                                    DB::raw('AVG(rate) AS average_rate, SUM(quantity) AS quantity_sold, SUM(sub_total) AS total_amount ') )
                                    ->orderBy('total_amount', 'DESC') ;          

     
        $number_of_records  = $q->get()->count();

        if ($search_key)
        {
            $query->where('description', 'like', like_search_wildcard_gen($search_key));
                
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();
//

        $rec = [];

        if (count($data) > 0) 
        {

            $quantity_sold              = 0;
            $total_amount               = 0;
            $average_rate               = 0;
            $currency                   = Currency::find($currency_id);
            $currency_symbol            = (count($currency) > 0) ? $currency->symbol : NULL ;

            foreach ($data as $key => $row) 
            {        

                $rec[] = array(                      
                    $row->description,
                    $row->quantity_sold,
                    format_currency($row->total_amount,TRUE , $currency_symbol ),
                    format_currency($row->average_rate,TRUE , $currency_symbol ),
                );

                $quantity_sold   += $row->quantity_sold;
                $total_amount    += $row->total_amount;
                $average_rate    += $row->average_rate;

          
            }

            array_push($rec, [

               '<b>'. __('form.total_per_page'). '<b>',
                '<b>'.$quantity_sold,
                '<b>'.format_currency($total_amount,TRUE, $currency_symbol ). '<b>',
                '<b>'.format_currency($average_rate,TRUE, $currency_symbol ). '<b>',

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


    function process_payment_request(Request $request)
    {

        error_log("test process pyament");
        /* 
        The following regex will hold for quantities like '12' or '12.5' or '12.05'. If you want more decimal points than two, 
        replace the "2" with the allowed decimals you need.
        */
        $validator = Validator::make($request->all(), [
            'amount'           => 'required|regex:/^\d*(\.\d{1,2})?$/',          
            'invoice_id'       => 'required',
            'gateway'          => 'required',

        ]);

        $previous_url = \URL::previous(); 
    
        if ($validator->fails()) 
        {        
            error_log("google");
            if(($previous_url != route('dashboard')) )
            {                
                // Redirect back
                Session::flash('message', __('form.could_not_process_the_payment'));
                Session::flash('alert-class', 'alert-danger');  
                error_log("first");          
                return redirect()->back()->withInput();
            }
            else
            {
                error_log("first else");
                abort(404);
            }
        }


       try{
            // Dycrypt the input to get the Invoice ID
            $invoice_id     = decrypt($request->invoice_id);
       }
       catch(\Exception $e){
       
            if(($previous_url != route('dashboard')) )
            {                
                error_log("second");           
                return redirect()->back()->withInput();
            }
            else
            {
                error_log("second else");
                abort(404);
            }
       }
              
        // Get instance of Invoice Class
        $invoice        = Invoice::find($invoice_id);

        // Go through the process if invoice record exists
        if($invoice)
        {
            error_log("invoice");
             // Calculating the amount due for the invoice , the following function is in Helper.php file       
            $amount_due    = calculate_invoice_amount_due($invoice->total, $invoice->amount_paid, $invoice->applied_credits);

            $amount = $invoice->validate_payment_amount($request->amount);

            if(!$amount)
            { 
                // Redirect Back
                Session::flash('message', __('form.under_received_amount'));
                Session::flash('alert-class', 'alert-danger'); 
                error_log("third");
                return redirect()->back()->withInput();
                
            }          

            $gateway = PaymentMode::get_class_instance_gateway_plugin($request->gateway);

            if($gateway)
            {
                $currency                   = $invoice->get_currency();
                
                $data['amount']             = $amount;   
                $data['currency_symbol']    = $currency['symbol'];
                $data['currency_iso']       = $currency['iso'];
                error_log("fourth");  
                error_log(json_encode($invoice));
                error_log(json_encode($data));
                return $gateway->process_payment($invoice, $data);     
                 
            }              
            
        }
        // Return Response
        Session::flash('message', __('form.could_not_process_the_payment'));
        Session::flash('alert-class', 'alert-danger'); 
        error_log("end");
        return redirect()->back()->withInput();

    }

    function process_paypal_payment(Request $request)
    {   
        error_log("number");
       
        error_log(json_encode($request));
        error_log("end");
        $invoice_id     = decrypt($request->invoice_id);
        $invoice        = Invoice::find($invoice_id);
        error_log("currency");
        error_log(json_encode($invoice->currency->code));
        $currency = $invoice->currency->code;
        $data = [];
        $amount = $invoice->validate_payment_amount(Input::get('stripeAmount'));
        
        $data['items'] = [
            [
                'name' => Config('APP_NAME'),
                'price' => $amount,
                'qty' => 1
            ]
        ];

        $data['invoice_id'] = time();

        $data['invoice_description'] = "Order #{$data['invoice_id']} Invoice";
        $data['return_url'] = url('/process/paypal/success',$invoice_id);
        $data['cancel_url'] = url('register');
        Session::put('invoice_id',$invoice_id);
        Session::put('amount',$amount);
        

        $data['total'] = $amount; 
        error_log("date check");
        error_log($data['total']);

        self::$provider->setCurrency($currency);
        $response = self::$provider->setExpressCheckout($data); 
        return redirect($response['paypal_link']);
    }

    function process_paypal_success(Request $request, $id)
    {   
        error_log("check visit");

        
        $invoice_id = Session::get('invoice_id');
        $invoice        = Invoice::find($invoice_id);
        error_log(json_encode($invoice_id));

        self::$provider->setCurrency($invoice->currency->code);
        $response = self::$provider->getExpressCheckoutDetails($request->token);
        error_log(json_encode($response));
        if($response['ACK'] == 'Success'){

            /*added vincy*/
            $data = [];
            $data['items'] = [
                        [
                            'name' => Config('APP_NAME'),
                            'price' => Session::get('amount'),
                            'qty' => 1
                        ]
                    ];

            $data['invoice_id'] = $invoice_id;
            $data['invoice_description'] = "Order #{$invoice_id} Invoice";
            $data['return_url'] = url('/process/paypal/success',$id);
            $data['cancel_url'] = url('register');

            $data['total'] = Session::get('amount');

            $response = self::$provider->doExpressCheckoutPayment($data, $request->token, $request->PayerID);
            /**/

    
            if($response['ACK'] == 'Success'){
                if($invoice)
                {      

                    $amount = $invoice->validate_payment_amount(Input::get('stripeAmount'));
                    error_log($amount);
                    $amount = Session::get('amount');
                    if(!$amount)
                    {
                        // Redirect Back
                        Session::flash('message', __('form.under_received_amount'));
                        Session::flash('alert-class', 'alert-danger');                
                    }
                    else
                    {
                        // $stripe = new \App\Services\PaymentGateway\PayPal();

                        // if($stripe->charge($invoice, $request))
                        // {

                            Session::flash('message', __('form.payment_successfully_processed'));
                            Session::flash('alert-class', 'alert-success');                
                        // }
                        // else
                        // {
                        //     Session::flash('message', __('form.could_not_process_the_payment'));
                        //     Session::flash('alert-class', 'alert-danger'); 
                            
                        // }
                    }            

                    
                }
            } 
            return redirect()->route('invoice_customer_view', [$invoice->id, $invoice->url_slug]);  
        }   else {
            return redirect('login');
        }   
        
        
    }

    function process_stripe_payment(Request $request)
    {
        error_log("check paypal");
        ini_set('max_execution_time', 300); //300 seconds = 5 minutes
        set_time_limit(0);
        /* 
        The following regex will hold for quantities like '12' or '12.5' or '12.05'. If you want more decimal points than two, 
        replace the "2" with the allowed decimals you need.
        */
        $validator = Validator::make($request->all(), [
            'invoice_id'              => 'required',
            'stripeToken'             => 'required',
            'stripeEmail'             => 'required|email',
            'stripeAmount'            => 'required|regex:/^\d*(\.\d{1,2})?$/', 
            

        ]);

        if ($validator->fails()) 
        {
            // Redirect back
            Session::flash('message', __('form.could_not_process_the_payment') . " validation ");
            Session::flash('alert-class', 'alert-danger'); 
            return redirect()->back();
        }
     

        // Get instance of Invoice Class
        $invoice        = Invoice::find(decrypt($request->invoice_id));

        // Go through the process if invoice record exists
        if($invoice)
        {      

            $amount = $invoice->validate_payment_amount(Input::get('stripeAmount'));

            if(!$amount)
            {
                // Redirect Back
                Session::flash('message', __('form.under_received_amount'));
                Session::flash('alert-class', 'alert-danger');                
            }
            else
            {
                $stripe = new \App\Services\PaymentGateway\Stripe();

                if($stripe->charge($invoice, $request))
                {

                    Session::flash('message', __('form.payment_successfully_processed'));
                    Session::flash('alert-class', 'alert-success');                
                }
                else
                {
                    Session::flash('message', __('form.could_not_process_the_payment'));
                    Session::flash('alert-class', 'alert-danger'); 
                    
                }
            }            

            return redirect()->route('invoice_customer_view', [$invoice->id, $invoice->url_slug]);
        }

        

        // Return Response
        Session::flash('message', __('form.could_not_process_the_payment'));
        Session::flash('alert-class', 'alert-danger'); 
        return redirect()->back();
    }



    function settings()
    {
        $records = Setting::whereIn('option_key', ['terms_invoice'])->get();      
        $rec = [];
        if(count($records) > 0)
        {
            $rec            = new \stdClass();
            foreach ($records as $row) 
            {
                $rec->{$row->option_key} = $row->option_value;  
            }  

        }       
        $data = [];
       return view('invoice.settings_invoice', compact('data'))->with('rec', $rec);
    }

    function update_settings(Request $request)
    {

        $obj = Setting::updateOrCreate(['option_key' => 'terms_invoice' ]);
        $obj->option_value = Input::get('terms_invoice');
        $obj->save();

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();
    }


    public function update_recurring_invoice_setting(Request $request)
    {
        $rules = [
        
            'id'                        =>  'required',
            'recurring_invoice_type'    => 'required',  
            
        ];

        if($request->recurring_invoice_type == 'custom')
        {
            $rules = $rules + [

                'recurring_invoice_custom_type'         =>  'required',
                'recurring_invoice_custom_parameter'    =>  'required',
            ];
        }

        $validator = Validator::make($request->all(), $rules, [

            'recurring_invoice_custom_type.required' => 'Frequency field is required',
            'recurring_invoice_custom_parameter.required' => 'Frequency value field is required',

        ]);

        
        if ($validator->fails()) 
        {
           if(isset($validator->messages()->all()[0]))
           {
                return response()->json(['status' => 2 ,'msg'=> $validator->messages()->all()[0] ]);
           }
           else
           {
                return response()->json(['status' => 2, 'msg' => __('form.could_not_perform_the_requested_action') ]);
           }
            
        }

        // Saving Data
        try{

            $obj                                                    = Invoice::find($request->id);

            if($obj)
            {
                $obj->recurring_invoice_type                        = $request->recurring_invoice_type;
                $obj->recurring_invoice_total_cycle                 = $request->recurring_invoice_total_cycle;
                $obj->is_recurring_invoice_period_infinity          = ($request->is_recurring_invoice_period_infinity == 'false') ? NULL : TRUE;
                $obj->recurring_invoice_custom_parameter            = $request->recurring_invoice_custom_parameter;
                $obj->recurring_invoice_custom_type                 = $request->recurring_invoice_custom_type;
                $obj->save();

                return response()->json(['status' => 1, 'msg' => __('form.success_update') ]);
            }

        }
        catch(\Exception $e)
        {
            return response()->json(['status' => 2, 'msg' => __('form.could_not_perform_the_requested_action') ]);
        }

    }


    function create_invoice_for_a_project(Request $request, Project $project)
    {   


        $validator = Validator::make($request->all(), [
                'invoice_record_style'      => 'required', 
                'task_ids'                  => 'required'       
          
            ]);      

        if ($validator->fails()) 
        {
            session()->flash('message', __('form.no_item_was_found_for_invoicing'));
            return redirect()->back();
                
        }

        $data = Invoice::dropdown();
        $items = [];

        if($request->invoice_record_style == 'all_timesheets_individually')
        {
            $timesheets     = Timesheet::whereIn('task_id', $request->task_ids)->whereNull('invoice_id')->orderBy('task_id')->get();

            if(count($timesheets) > 0)
            {
                foreach ($timesheets as $timesheet) 
                {
                    if($timesheet->task->is_billable)
                    {
                        $rate =   ($project->billing_type_id == BILLING_TYPE_PROJECT_HOURS) ? $project->billing_rate : $timesheet->task->hourly_rate;

                        $sub_total = $project->calculate_task_cost($timesheet->duration, $rate);

                        $items[] = [

                                    'description'       => $timesheet->task->title. " - " . $timesheet->duration,
                                    'long_description'  => '',
                                    'quantity'          => time_to_decimal($timesheet->duration),
                                    'unit'              => '',
                                    'rate'              => $rate,
                                    'tax_id'            => '',
                                    'sub_total'         => $sub_total,
                                    'component_id'      => COMPONENT_TYPE_TIMESHEET,
                                    'component_number'  => $timesheet->id
                                ];
                    }                   
                    

                    
                }
            }
        }
        else if($request->invoice_record_style == 'task_per_item')
        {
            $tasks          = Task::whereIn('id', $request->task_ids)->get();
            // $timesheets     = Timesheet::whereIn('task_id', $request->task_ids)

            if(count($tasks) > 0)
            {

                foreach ($tasks as $task) 
                {
                    $timesheets = $task->timesheets()->whereNull('invoice_id')->orderBy('task_id')->get();
                    $rate =   ($project->billing_type_id == BILLING_TYPE_PROJECT_HOURS) ? $project->billing_rate : $task->hourly_rate;
                    
                    $duration = [];

                    if($task->is_billable && count($timesheets) > 0)
                    {
                        foreach ($timesheets as $timesheet) 
                        {
                            $duration[] = $timesheet->duration;                                                    
                        }

                        $duration = $project->sum_time($duration);

                        $items[]  = [
                                    'description'       => $project->name . " - " .$task->title ." - ". $duration,
                                    'long_description'  => '',
                                    'quantity'          => time_to_decimal($duration),
                                    'unit'              => '',
                                    'rate'              => $rate,
                                    'tax_id'            => '',
                                    'sub_total'         => $project->calculate_task_cost($duration, $rate),
                                    'component_id'      => COMPONENT_TYPE_TASK,
                                    'component_number'  => $task->id
                        ];
                    }
                }
            }


        }
        else
        {
            // single_line
            $items[]  = [
                            'description'       => $project->name,
                            'long_description'  => '',
                            'quantity'          => 1,
                            'unit'              => '',
                            'rate'              => $project->billing_rate,
                            'tax_id'            => '',
                            'sub_total'         => $project->billing_rate,
                            'component_id'      => COMPONENT_TYPE_PROJECT,
                            'component_number'  => $project->id
                        ];
        }
        


        if(is_array($request->expense_ids) && (count($request->expense_ids) > 0) && ($request->invoice_record_style != 'single_line') )
        {
            // 
            $expenses = $project->unbilled_expenses()->whereIn('id', $request->expense_ids)->get();
            
            if(count($expenses) > 0)
            {
                foreach ($expenses as $expense) 
                {
                    $items[]  = [
                            'description'       => $project->name . " - " . $expense->category->name . " - " . $expense->name,
                            'long_description'  => $expense->note,
                            'quantity'          => 1,
                            'unit'              => '',
                            'rate'              => $expense->amount,
                            'tax_id'            => '',
                            'sub_total'         => $expense->amount,
                            'component_id'      => COMPONENT_TYPE_EXPENSE,
                            'component_number'  => $expense->id
                        ];
                }
            }
        }

        $data['customer_id_list']   = [$project->customer->id => $project->customer->name];

        $data['project_id_list']    = [$project->id => $project->name];  

       
        $rec = new \stdClass();
       
        $rec->terms_and_condition   = get_setting('terms_invoice');
        $rec->items                 = $items;

        $rec->currency_id           = ($project->customer->currency_id) ? $project->customer->currency_id : config('constants.default_currency_id');

        $rec->invoicing_for_project = TRUE;

        session(['invoicing_for_project' => [ 'data' => $data, 'rec' => $rec] ]);

        // return view('invoice.create', compact('data'))->with('rec', $rec);
        return redirect()->route('add_invoice_page');



       
    }



    function get_child_invoices()
    {    

        $invoice    = Invoice::find(Input::get('invoice_id'));

        $data = $invoice->children()->get();

        $rec = [];

        if ($data) 
        {
            foreach ($data as $key => $row) 
            {

                $rec[] = array(

                    'number'        => anchor_link($row->number, route('show_invoice_page', $row->id)) ,
                    'total'         => format_currency($row->total, TRUE, $row->get_currency_symbol()),
                    'tax_total'     => format_currency($row->tax_total, TRUE, $row->get_currency_symbol()) ,
                    'date'          => isset(($row->date)) ? sql2date($row->date) : "" ,                  
                    'due_date'      => isset(($row->due_date)) ? sql2date($row->due_date) : "",
                    'status'        => $row->status->name,

                );

            }
        }


        $output = array(     
            "data" => $rec
        );


        return response()->json($output);


    }

    function recurring_invoices()
    {

        return view('invoice.recurring');
    }

    function paginate_recurring_invoices()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];


        $q                  = Invoice::whereNotNull('recurring_invoice_type')->where('recurring_invoice_type', '<>', 0);
        $query              = Invoice::whereNotNull('recurring_invoice_type')->where('recurring_invoice_type', '<>', 0)
                                ->orderBy('id', 'DESC')->with(['status', 'tags', 'customer', 'project']);

        // If the user has permission to view only the ones that are created by himself;
        if(!check_perm('invoices_view') && check_perm('invoices_view_own'))
        {
            $q->where(function($k){
                $k->where('created_by', auth()->user()->id);
            });
            $query->where(function($k){
                $k->where('created_by', auth()->user()->id);
            });                   
            
        }



        // End of data Filtering

        $number_of_records  = $q->get()->count();

        if ($search_key)
        {
            $query->where(function ($k) use ($search_key) {
                $k->where('number', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('total', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('tax_total', 'like', like_search_wildcard_gen($search_key))
                    ->orWhere('date', 'like', like_search_wildcard_gen(date2sql($search_key)))
                    ->orWhere('due_date', 'like', like_search_wildcard_gen(date2sql($search_key)))
                   ->orWhere('reference', 'like', like_search_wildcard_gen($search_key))
                    ->orWhereHas('customer', function ($q) use ($search_key) {
                        $q->where('customers.name', 'like', $search_key . '%');
                    })
                     ->orWhereHas('project', function ($q) use ($search_key) {
                        $q->where('projects.name', 'like', $search_key . '%');
                    })
                    ->orWhereHas('tags', function ($q) use ($search_key) {
                        $q->where('name', 'like', $search_key . '%');
                    })
                    ->orWhereHas('status', function ($q) use ($search_key) {
                        $q->where('name', 'like', $search_key . '%');
                });
            });
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();
//

        $rec = [];

        if (count($data) > 0) {
            foreach ($data as $key => $row) 
            {

                $act = [];
                array_push($act,  [
                    'action_link' => route('invoice_customer_view', [$row->id, $row->url_slug]), 
                    'action_text' => __('form.view'), 'action_class' => '', 'new_tab' => TRUE,
                    'permission' => 'invoices_view',
                ]);

                // 
                if(!in_array($row->status_id, [ INVOICE_STATUS_PARTIALLY_PAID , INVOICE_STATUS_PAID ]))
                {
                    array_push($act, [
                        'action_link' => route('edit_invoice_page', $row->id), 
                        'action_text' => __('form.edit'), 'action_class' => '',
                        'permission' => 'invoices_edit',
                    ]);
                }
                

                $rec[] = array(

                    a_links(vue_click_link($row->number, $row->id, route('show_invoice_page', $row->id)), $act),
                    format_currency($row->total, TRUE, $row->get_currency_symbol()),
                    format_currency($row->tax_total, TRUE, $row->get_currency_symbol()) ,
                    isset(($row->date)) ? sql2date($row->date) : "",
                    anchor_link($row->customer->name, route('view_customer_page', $row->customer_id )),
                                 
                    isset($row->project->name) ? anchor_link($row->project->name, route('show_project_page', $row->project->id)) : "",
                    $row->get_tags_as_badges(true),    
                    isset(($row->due_date)) ? sql2date($row->due_date) : "",

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


    
}

