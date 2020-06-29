<?php

namespace App\Http\Controllers;

use App\Customer;

use App\Estimate;
use App\Invoice;
use App\Item;
use App\User;
use App\Lead;

use App\Proposal;
use App\ProposalItem;
use App\ProposalStatus;
use App\Services\Pdf;
use App\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendProposal;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ProposalAccepted;
use App\Notifications\ProposalDeclined;
use App\Services\CommonSalesJobs;
use App\NumberGenerator;

class ProposalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['proposal_short_codes']               = Proposal::proposal_short_codes();
        $rec['item_statuses']                       = ProposalStatus::all();

        $data['proposal_statuses_id_list']          = ProposalStatus::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        $data['default_proposal_status_id_list']    = [
            PROPOSAL_STATUS_DRAFT, 
            PROPOSAL_STATUS_SENT    , 
            PROPOSAL_STATUS_OPEN , 
            PROPOSAL_STATUS_REVISED,
           
        ];

        if($email = get_setting('email_template_proposal_sent_to_customer'))
        {
            $data['email_template'] = (isset($email->template)) ? $email->template : "";
        }
        else
        {
           $data['email_template']  = "";
        }

        return view('proposal.index', compact('rec', 'data'));
    }

    function paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $customer_id        = Input::get('customer_id');
        $lead_id            = Input::get('lead_id');
        
        $status_id          = Input::get('status_id');
        $q                  = Proposal::query();
        $query              = Proposal::orderBy('id', 'DESC')->with(['status', 'tags']);

         // If the user has permission to view only the ones that are created by himself;
        if(!check_perm('proposals_view') && check_perm('proposals_view_own'))
        {
            $q->where(function($k){
                $k->where('created_by', auth()->user()->id)->orWhere('assigned_to', auth()->user()->id);
            });
            $query->where(function($k){
                $k->where('created_by', auth()->user()->id)->orWhere('assigned_to', auth()->user()->id);
            });                   
            
        }

        if($customer_id)
        {
            $q->where('component_id', COMPONENT_TYPE_CUSTOMER)
                ->where('component_number', $customer_id);

            $query->where('component_id', COMPONENT_TYPE_CUSTOMER)
                ->where('component_number', $customer_id);
        }

        if($lead_id)
        {
            $q->where('component_id', COMPONENT_TYPE_LEAD)
                ->where('component_number', $lead_id);

            $query->where('component_id', COMPONENT_TYPE_LEAD)
                ->where('component_number', $lead_id);
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

             $k->where('number', 'like', $search_key.'%')
                ->orWhere('title', 'like', $search_key.'%');

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
                    a_links(
                        vue_click_link($row->number, $row->id ),
                        [
                            [
                                'action_link' => route('edit_proposal_page', $row->id), 
                                'action_text' => __('form.edit'), 'action_class' => '',
                                'permission' => 'proposals_edit',
                            ],
                            [
                                'action_link' => route('delete_proposal', $row->id), 
                                'action_text' => __('form.delete'), 'action_class' => 'delete_item',
                                'permission' => 'proposals_delete',
                            ]
                    ]),
                    $row->title,
                    $row->send_to,
                    format_currency($row->total, TRUE, $row->get_currency_symbol() ),
                    sql2date($row->date),
                    sql2date($row->open_till),
                    $row->get_tags_as_badges(true),
                    sql2date($row->created_at),
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


        $data = Proposal::dropdown();
        $rec = new \stdClass();
        $rec->currency_id = config('constants.default_currency_id');

        if(isset($request['from']) && $request['from'] == 'lead' && isset($request['id']) && $request['id'])
        {
            $lead = Lead::find($request['id']);

            if(isset($lead->id))
            {   
                $name                                   = $lead->first_name . " ". $lead->last_name;
                $rec->component_id                      = COMPONENT_TYPE_LEAD;
                $data['component_number_options']       = [ $lead->id => $name] ;
                $rec->email                             = $lead->email;
                $rec->send_to                           = $name;
                $rec->address                           = $lead->address;
                $rec->city                              = $lead->city;
                $rec->state                             = $lead->state;
                $rec->zip_code                          = $lead->zip_code;
                $rec->country_id                        = $lead->country_id;          
                $rec->phone                             = $lead->phone;
            }

            
        }

        return view('proposal.create', compact('data'))->with('rec', $rec);
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

            'title' => 'required|max:190',
            'date' => 'required',
            'component_id' => 'required',
            'component_number' => 'required',
            'currency_id' => 'required',
            'send_to' => 'required',
            'email' => 'required|email',

        ];
        $msg = [
            'component_id.required' => __('form.related_to_field_is_required'),
            'component_number.required' => __('form.this_field_is_required'),
            'currency_id.required' => __('form.currency_field_is_required'),
            'to.required' => __('form.to_field_is_required'),
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
            $request['number']              = NumberGenerator::gen(COMPONENT_TYPE_PROPOSAL);
            $request['content']             = get_setting('template_proposal');
            $request['date']                = date2sql($request->date) ;
            $request['open_till']           = ($request->open_till) ? date2sql($request->open_till) : NULL ;
            $request['tax_total']           = calculate_tax_total($request->taxes);
            $request['taxes']               = (!empty($request->taxes)) ? json_encode($request->taxes) : NULL;            
            $request['created_by']          = auth()->user()->id;

            // Inserting the proposal   
            $proposal  = Proposal::create($request->all());

            // Inserting Product Items in proposal_items table
            $common_sales_jobs = new CommonSalesJobs($proposal);
            $common_sales_jobs->insert_item_line($request, ProposalItem::class ,'proposal_id');

            // Attaching Tags             
            $proposal->tag_attach($request->tag_id);
            
            // Log Activity
            $description = sprintf(__('form.act_created') , __('form.proposal'));
            log_activity($proposal, $description, anchor_link($proposal->title, route('show_proposal_page', $proposal->id ))  );

            DB::commit();
            $success = true;

        } 
        catch (\Exception  $e) 
        {   
            $success = false;
            DB::rollback();            
         
        }

        if ($success) {
            // the transaction worked ...
            session()->flash('message', __('form.success_add'));
            return redirect()->route('show_proposal_page', $proposal->id );
        } else {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('proposal_list');
        }



    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Proposal $proposal)
    {

        //return view('proposal.show', compact('data'))->with('rec', $proposal);

    }

    

    public function edit(Proposal $proposal)
    {        

        $data = Proposal::dropdown();

        $common_sales_jobs                  = new CommonSalesJobs($proposal);

        // Merging Tax Dropdown Information
        $data['tax_id_list']                = $common_sales_jobs->merge_tax_dropdown_information($data['tax_id_list']);

        $proposal->items                    = $proposal->item_line()->get();
        $proposal->tag_id                   = $proposal->tags()->pluck('tag_id')->toArray();

        $data['component_number_options']   = [ $proposal->related_to->id => $proposal->related_to->name] ;

        return view('proposal.create', compact('data'))->with('rec',$proposal );
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

            'title' => 'required|max:190',
            'date' => 'required',
            'component_id' => 'required',
            'component_number' => 'required',
            'currency_id' => 'required',
            'send_to' => 'required',
            'email' => 'required|email',

        ];
        $msg = [
            'component_id.required' => __('form.related_to_field_is_required'),
            'component_number.required' => __('form.this_field_is_required'),
            'currency_id.required' => __('form.currency_field_is_required'),
            'to.required' => __('form.to_field_is_required'),
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

            $obj                             = Proposal::find($id); 
            $request['date']                 = date2sql($request->date) ;
            $request['open_till']            = ($request->open_till) ? date2sql($request->open_till) : NULL ;
            $request['tax_total']            = calculate_tax_total($request->taxes);   
            $request['taxes']                = (!empty($request->taxes)) ? json_encode($request->taxes) : NULL;
                 

            // Update Proposal
            $obj->update($request->all()) ;            
            
            // Update Proposal Item Line
            $common_sales_jobs = new CommonSalesJobs($obj);
            $common_sales_jobs->update_item_line($request->items, ProposalItem::class, 'proposal_id');

            // Update Tags
            $obj->tag_sync($request->tag_id);
  
            // Log Activity
            $description = sprintf(__('form.act_updated'), __('form.proposal'));            
            log_activity($obj, $description, anchor_link($obj->title, route('show_proposal_page', $obj->id )) ) ;

            DB::commit();

            $success = true;

        } 
        catch (\Exception  $e) 
        {
            $success = false;
            DB::rollback();
        
        }

        if ($success) 
        {
            // the transaction worked ...
            session()->flash('message', __('form.success_update'));
            return redirect()->route('proposal_list');
        } 
        else 
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('proposal_list');
        }



    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Proposal $proposal)
    {
        DB::beginTransaction(); 

        try {                 

            // Remove Proposal Items
            $items = $proposal->item_line()->get();  

            foreach ($items as $item) 
            {
               $item->forcedelete();
            }    
            
            // Remove Tags
            $proposal->tag_sync([]);

            // Finally Remove the Proposal
            $proposal->forcedelete();

            // Log Activity
            $description    = sprintf(__('form.act_deleted'), __('form.proposal'));       
            log_activity($proposal, trim($description), $proposal->title);  

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

        return redirect()->route('proposal_list');

    }

    function related_component()
    {
        $search_key = Input::get('search');
        $component_type = Input::get('type');


        if($component_type == COMPONENT_TYPE_CUSTOMER)
        {

            $data = Customer::where('name', 'like', $search_key.'%')->with(['primary_contact'])->get();

            if(count($data) > 0)
            {
                foreach ($data as $key=>$row)
                {
                    $data[$key]['contact_name'] = $row->primary_contact->first_name . " ".  $row->primary_contact->last_name;
                    $data[$key]['email'] = $row->primary_contact->email;
                }

            }

        }
        elseif ($component_type == COMPONENT_TYPE_LEAD)
        {
            $data = Lead::where('first_name', 'like', $search_key.'%')->orWhere('last_name', 'like', $search_key.'%')->get();

            if(count($data) > 0)
            {
                foreach ($data as $key=>$row)
                {
                    $data[$key]['name'] = $row->first_name . " ".  $row->last_name;
                    $data[$key]['contact_name'] = $row->first_name . " ".  $row->last_name;
                    $data[$key]['email'] = $row->email;
                }

            }

        }

        $results = ($data->count() > 0) ? $data : [];

        return response()->json([
            'results' => $results
        ]);
    }

    function search_product()
    {
        $search_key = Input::get('search');

        $data = Item::where('items.name', 'like', $search_key.'%')
            ->select('items.id AS id', 'items.name', 'description', 'items.rate', 'unit', 'taxes_1.display_as AS tax_id_1', 'taxes_2.display_as AS tax_id_2')
            ->leftJoin('taxes as taxes_1', 'taxes_1.id', '=', 'items.tax_id_1')
            ->leftJoin('taxes as taxes_2', 'taxes_2.id', '=', 'items.tax_id_2')
            ->get();

        $results = ($data->count() > 0) ? $data : [];

        return response()->json([
            'results' => $results
        ]);
    }


    function get_proposal_details_ajax()
    {

        $id = Input::get('id');

        $rec = Proposal::find($id);


        $returnHTML = view('proposal.partials.show.proposal_header', compact('rec'))->render();


        if($rec->component_id == COMPONENT_TYPE_CUSTOMER)
        {
            // It is Customer
            $rec->is_customer = true;
        }
        else
        {
            // It is Lead
            $lead = $rec->related_to()->get()->first();
            $rec->is_customer = (isset($lead->customer_id) && $lead->customer_id) ?  TRUE: FALSE;
        }


        // If Proposal was converted to Invoice/Estimate
        if(($rec->status_id == PROPOSAL_STATUS_ACCEPTED) && $rec->converted_to)
        {
            $rec->hide_status_dropdown = TRUE;
            $rec->hide_convert_to_button = TRUE;

            if($rec->converted_to == COMPONENT_TYPE_ESTIMATE)
            {
                $url = route('estimate_list');
                $conv_obj = Estimate::find($rec->converted_to_id);
            }
            else
            {
                $url = route('invoice_list') ;
                $conv_obj = Invoice::find($rec->converted_to_id);
            }
            
            if(isset($conv_obj->number))
            {
                $rec->link_to_converted_component = $url."?id=". $rec->converted_to_id;
                $rec->link_text = $conv_obj->number;
            }
            
        }
        else
        {
            $rec->hide_status_dropdown = FALSE;
            $rec->hide_convert_to_button = FALSE;
            $rec->link_to_converted_component = "";
            $rec->link_text = "";
        }


        return response()->json(
            array(
                'status' => 1,
                'html' => $returnHTML,
                'records' => $rec,
                'proposal_content' => $rec->content,
                'url_slug' => $rec->url_slug,
                'item_status' => [
                    'name' => $rec->status->name,
                    'id' => $rec->status_id
                ],
                'url_to_proposal_customer_view' => route('proposal_customer_view', [$rec->id, $rec->url_slug ])

            )
        );
    }

    function get_proposal_items_ajax()
    {

        $id = Input::get('id');

        $rec = Proposal::find($id);

        $rec->array_of_taxes_used = [];
        if (isset($rec->taxes) && $rec->taxes) {
            $rec->array_of_taxes_used = json_decode($rec->taxes);

        }

        $returnHTML = view('proposal.partials.show.proposal', compact('rec'))->render();

        return response()->json(
            array(
                'status' => 1,
                'html' => $returnHTML,
                'item_status' => [
                    'name' => $rec->status->name,
                    'id' => $rec->status_id
                ]
            )
        );
    }

    function save_proposal_content(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'content' => 'required',

        ]);

        if ($validator->fails())
        {

            return response()->json(['status' => 2]);
        }

        $proposal = Proposal::find(Input::get('id'));
        $proposal->content = Input::get('content');
        $proposal->save();

        return response()->json(['status' => 1]);
    }

    function customer_view($id)
    {

        $rec = Proposal::find($id);

        if($rec)
        {
            $rec->array_of_taxes_used = [];
            
            if (isset($rec->taxes) && $rec->taxes) 
            {
                $rec->array_of_taxes_used = json_decode($rec->taxes);
            }

            
            $data['header'] = view('proposal.partials.show.proposal_header', compact('rec'))->render();
            $data['proposal_items'] = view('proposal.partials.show.proposal_items', compact('rec'))->render();


            $rec->content = $this->replace_short_codes_with_data($data['proposal_items'], $rec);


            return view('proposal.customer_view', compact('data'))->with('rec', $rec );
        }
        else
        {
            abort(404);
        }

        
    }

    function download_proposal($id, $for_email = NULL)
    {
        if ($id) 
        {
            $rec = Proposal::find($id);

            if($rec)
            {
                $rec->array_of_taxes_used = [];
                
                if (isset($rec->taxes) && $rec->taxes) 
                {
                    $rec->array_of_taxes_used = json_decode($rec->taxes);

                }
                
                $data['proposal_items'] = view('proposal.partials.show.proposal_items', compact('rec'))->render();

                $rec->content = $this->replace_short_codes_with_data($data['proposal_items'], $rec);

                $data['page_title'] = $rec->number;

                $data['html'] = view('proposal.partials.show.print', compact('rec'))->render();

               

                $html = view('layouts.print.template', compact('data'))->render();


               $file_name = str_replace(" ", "_", trim($data['page_title']));

               $pdf = new Pdf();

               if($for_email)
               {
                    return $pdf->get_pdf_file_path($html);
               }

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



    function replace_short_codes_with_data($proposal_items, $all_records)
    {
        $rec = $all_records;

        $replacements = array(
        'proposal_items'        => $proposal_items,
        'proposal_number'       => $rec->number,
        'proposal_title'        => $rec->title,
        'proposal_total'        => format_currency($rec->total, true),
        'proposal_subtotal'     => format_currency($rec->sub_total, true),
        'proposal_open_till'    => sql2date($rec->open_till),
        
        'proposal_proposal_to'  => $rec->related_to->name,
        'proposal_address'      => (isset($rec->related_to->address) && $rec->related_to->address) ? nl2br($rec->related_to->address) : '',
        'proposal_city'         => (isset($rec->related_to->city) && $rec->related_to->city) ? $rec->related_to->city : '',
        'proposal_state'        => (isset($rec->related_to->state) && $rec->related_to->state) ? $rec->related_to->state : '',
        'proposal_zip'          => (isset($rec->related_to->zip_code) && $rec->related_to->zip_code) ? $rec->related_to->zip_code : '',
        'proposal_country'      => (isset($rec->related_to->country->name) && $rec->related_to->country->name) ? $rec->related_to->country->name : '',
           
        );


        if(isset($rec->content) && $rec->content)
        {
            $rec->content = preg_replace_callback(
                '/{[^}]*\}/',
                function (array $m) use ($replacements) {
                    $item = strtr(trim($m[0]), ['{' => '', '}' =>'']);
                    return array_key_exists($item, $replacements) ? $replacements[$item] : '';
                },
                $rec->content
            );

            libxml_use_internal_errors(true);
            $html = $rec->content;
            $dom = new \DOMDocument();
            $dom->loadHTML($html);
            $imgs = $dom->getElementsByTagName('img');
            foreach($imgs as $img) 
            {
                $img->setAttribute('class', $img->getAttribute('class') . ' img-fluid');
            }


//

            // Remove the unnecessary html tags created by DOMdocument above
            $rec->content = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace( array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $dom->saveHTML()));
        }

        return $rec->content;
    }

    function change_status()
    {
        $id = Input::get('id');
        $status_id = Input::get('status_id');

        if ($id && $status_id) {
            $obj = Proposal::find($id);
            $obj->status_id = $status_id;
            $obj->save();

            // Log Acitivity
            $description = sprintf( __('form.act_changed_status_of'), 
                        anchor_link($obj->number, route('show_proposal_page', $obj->id ))) . ' '. __('form.to') . ' '.  $obj->status->name;
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
        } else {
            return response()->json(
                array(
                    'status' => 2
                )
            );
        }
    }


    function send_to_email(Request $request)
    {
        $proposal_id        = Input::get('proposal_id');
        $email_cc           = Input::get('email_cc');
        $email_template     = Input::get('email_template');

        $proposal           = Proposal::with('customer')->find($proposal_id);
        
        $primary_contact    = $proposal->customer->primary_contact;

        $replacements = array(
            'contact_name'          => $primary_contact->first_name . " ". $primary_contact->last_name,
            'proposal_number'       => $proposal->number,
            'proposal_title'        => $proposal->title,
            'proposal_total'        => format_currency($proposal->total, true),            
            'proposal_open_till'    => sql2date($proposal->open_till),                        
            'email_signature'       => config()->get('constants.email_signature'),
            'proposal_link'         => route('proposal_customer_view', [$proposal->id, $proposal->url_slug ])
           
        );

        $email_template = short_code_parser($email_template, $replacements);
    
        $mail = Mail::to($proposal->email);

        // Include Email CC
        if($email_cc)
        {
            $mail->cc($email_cc);
        }
        
        $proposal->status_id = PROPOSAL_STATUS_SENT;
        $proposal->save();

        try{

            if(Input::get('add_attachment'))
            {
                $pdf_file_path = $this->download_proposal($proposal->id , TRUE);
                $mail->send(new SendProposal(['email_template' => $email_template], $proposal, $pdf_file_path ));
            }
            else
            {
                $mail->send(new SendProposal(['email_template' => $email_template] , $proposal ));
            }

            session()->flash('message', __('form.email_sent'));

        }
        catch(\Exception $e)
        {
           session()->flash('message', __('form.email_was_not_sent'));
        }


        
        return  redirect()->back();
    }




    function accept_proposal(Request $request, Proposal $proposal)
    {
        if($proposal->status_id != PROPOSAL_STATUS_ACCEPTED)
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
                $proposal->status_id                            = PROPOSAL_STATUS_ACCEPTED;
                $proposal->accepted_by_first_name               = $request->first_name;
                $proposal->accepted_by_last_name                = $request->last_name;
                $proposal->accepted_by_email                    = $request->email;
                $proposal->accepted_by_signature                = $file_name;
                $proposal->accepted_date                        = date('Y-m-d H:i:s');
                $proposal->save();


                // Notify Admin
                $user_ids[0] = $proposal->created_by;
                
                if($proposal->assigned_to)
                {
                    $user_ids[1] = $proposal->assigned_to;
                }

                $notifiable_users = User::whereIn('id', $user_ids)->get();

                Notification::send($notifiable_users, new ProposalAccepted($proposal));
                

                DB::commit();
                $success = true;

            } 
            catch (\Exception  $e) {
                $success = false;              
                DB::rollback();

            }

            if($success) 
            {
                Session::flash('proposal_flash_message', TRUE); 
                return response()->json(['status' => 1]);
            } 
            else 
            {                
                return response()->json(['status' => 3, 'msg' => __('form.could_not_perform_the_requested_action') ]);
            }

        }

        
    }

    function decline_proposal(Proposal $proposal)
    {
        if($proposal->status_id != PROPOSAL_STATUS_ACCEPTED)
        {

            DB::beginTransaction();
            $success = false;

            try {

                // Change Proposal's Status

                $proposal->status_id = PROPOSAL_STATUS_DECLINED;
                $proposal->save();

                // Notify Users
                $user_ids[0] = $proposal->created_by;
                
                if($proposal->assigned_to)
                {
                    $user_ids[1] = $proposal->assigned_to;
                }

                $notifiable_users = User::whereIn('id', $user_ids)->get();

                Notification::send($notifiable_users, new ProposalDeclined($proposal));

                Session::flash('proposal_flash_message', TRUE); 

                DB::commit();
                $success = true;
                
            }
            catch (\Exception  $e) {

                $success = false;
                DB::rollback();

            }

             
        }

       return  redirect()->back();
    }


    function settings()
    {
        $records    = Setting::whereIn('option_key', ['template_proposal'])->get();
        $rec        = [];

        if(count($records) > 0)
        {
            $rec            = new \stdClass();
            foreach ($records as $row) 
            {
                $rec->{$row->option_key} = $row->option_value;  
            }


        }      

        $data['short_codes_proposal_template'] = Proposal::proposal_short_codes();    
       

       return view('proposal.settings_proposal', compact('data'))->with('rec', $rec);
    }

    function update_settings(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //         'email_template_proposal'      => 'required',        
          
        //     ]);      

        // if ($validator->fails()) {
        //     return redirect()->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }
        

        $obj = Setting::updateOrCreate(['option_key' => 'template_proposal' ]);
        $obj->option_value = Input::get('template_proposal');
        $obj->save();

        

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();
    }
}
