<?php

namespace App\Http\Controllers;

use App\Expense;

use App\Rules\ValidDate;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('expense.index');
    }


    function paginate()
    {
        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $project_id         = Input::get('project_id');
        $customer_id        = Input::get('customer_id');
        $vendor_id          = Input::get('vendor_id');


        $q                  = Expense::query();
        $query = Expense::orderBy('id', 'DESC')
        ->with(['category', 'project', 'customer', 'payment_mode', 'invoice', 'vendor', 'currency']);

        // If the user has permission to view only the ones that are created by himself;
        if(!check_perm('expenses_view') && check_perm('expenses_view_own'))
        {
            $q->where(function($k){
                $k->where('user_id', auth()->user()->id);
            });
            $query->where(function($k){
                $k->where('user_id', auth()->user()->id);
            });                   
            
        }

        if($project_id)
        {
            $q->where('project_id', $project_id);
            $query->where('project_id', $project_id);
        }

        if($customer_id)
        {
            $q->where('customer_id', $customer_id);
            $query->where('customer_id', $customer_id);
        }

        if($vendor_id)
        {
            $q->where('vendor_id', $vendor_id);
            $query->where('vendor_id', $vendor_id);
        }
        

        $number_of_records  = $q->get()->count();


        

        if($search_key)
        {
            $query->where(function ($k) use ($search_key) {
                $k->where('note', 'like', like_search_wildcard_gen($search_key) )
                ->orWhere('date', 'like', like_search_wildcard_gen( date2sql($search_key)) )
                ->orWhere('amount', 'like', like_search_wildcard_gen( $search_key ) )
                ->orWhereHas('category', function ($q) use ($search_key) {
                    $q->where('expense_categories.name', 'like', like_search_wildcard_gen($search_key) );
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

                if($row->attachment)
                {
                    $attachment_url = '<a href="'.route('download_attachment_expense', Crypt::encryptString($row->attachment) ).'">'.__('form.download').'</a>';
                }

                // If the expense has a customer involvement and hasn't been invoiced, display that
                $invoice_status_text = '';
                if($row->customer_id && $row->is_billable && !$row->invoice_id)
                {
                    $invoice_status_text = '<br><span class="text-danger">'. __("form.not_invoiced").'</span>';
                }


                // If invoiced do not show the edit or delete link
                if($row->invoice_id)
                {
                    $action_link = [];
                    $invoice_status_text = '<br><span class="badge badge-success">'. __("form.invoiced").'</span>';
                }
                else
                {
                   $action_link = [                                  
                                    [
                                        'action_link'   => route('edit_expense_page', $row->id), 
                                        'action_text'   => __('form.edit'), 
                                        'action_class'  => '', 
                                        'permission'    => 'expenses_edit'
                                    ],
                                    [
                                        'action_link'   => route('delete_expense', $row->id), 
                                        'action_text'   => __('form.delete'), 
                                        'action_class'  => 'delete_item',
                                        'permission'    => 'expenses_delete'
                                    ]
                                ];
                }  

                $rec[] = array(
                    a_links(vue_click_link($row->category->name . $invoice_status_text, $row->id, route('expense_list'). '?id='.$row->id),
                            $action_link
                        ),



                    format_currency($row->amount_after_tax, TRUE, $row->currency->symbol),
                    $row->name,
                    sql2date($row->date) ,
                    (isset($row->project->name))    ? anchor_link($row->project->name, route('show_project_page', $row->project_id)) : '',
                    (isset($row->customer->name))   ? anchor_link($row->customer->name, route('view_customer_page', $row->customer_id)) : '',
                    (isset($row->invoice->number))  ? anchor_link($row->invoice->number, route('show_invoice_page', $row->invoice->id) ) : '',
                    (isset($row->vendor->name))     ? anchor_link($row->vendor->name, route('view_vendor_page', $row->vendor->id) ) : '',

                    
                    $row->reference,
                    (isset($row->payment_mode->name)) ? $row->payment_mode->name : '',
                    isset($attachment_url) ? $attachment_url : '',

                );

            }
        }


        $output = array(
            "draw"                => intval(Input::get('draw')),
            "recordsTotal"        => $number_of_records,
            "recordsFiltered"     => $recordsFiltered,
            "data"                => $rec
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
        $data = Expense::dropdown_expenses();
        
        return view('expense.create', compact('data'))->with('rec', []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'date'                          => ['required', new ValidDate()],
            'expense_category_id'           =>'required',
            'amount'                        => 'required|numeric',
            'name'                          => 'max:192',
            'note'                          => 'max:192',
            'attachment'                    => 'sometimes|required|max:1000|mimes:jpeg,bmp,png,doc,docx,pdf',
            'currency_id'                   =>'required',

        ]);

        if ($validator->fails()) {
            return  redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Upload Attachment
        $attachment = NULL;

        if ($request->hasFile('attachment'))
        {
            $attachment = Storage::putFile('public/expense', $request->file('attachment') );
        }

        $amount                     = remove_commas($request->amount) ;

        $obj = new Expense();
        $obj->expense_category_id   = $request->expense_category_id ;
        $obj->date                  = date2sql($request->date) ;
        $obj->amount                = $amount ;
        $obj->amount_after_tax      = Expense::calculate_amount_after_tax($request->tax_id, $amount);
        $obj->name                  = $request->name ;
        $obj->note                  = $request->note ;
        $obj->customer_id           = $request->customer_id ;
        $obj->vendor_id             = $request->vendor_id ;        
        $obj->project_id            = $request->project_id ;
        $obj->is_billable           = ($request->is_billable) ? $request->is_billable : NULL;
        $obj->currency_id           = $request->currency_id;
        $obj->payment_mode_id       = $request->payment_mode_id ;
        $obj->reference             = $request->reference ;
        $obj->tax_id                = isset($request->tax_id) ? json_encode($request->tax_id) :  NULL;

        if($attachment)
        {
            $obj->attachment        = $attachment ;
        }
        $obj->user_id               = auth()->id() ;
        $obj->save();


        // Log Activity   
        $description = sprintf(__('form.act_has_recorded_an_expense_in'), anchor_link($obj->category->name, route('show_expense_page', $obj->id ) ));
        log_activity($obj, trim($description));

        session()->flash('message', __('form.success_add'));
        return  redirect()->route('expense_list');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {


    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Expense $expense)
    {
        if($expense->invoice_id)
        {
            return redirect()->back();
        }

        $data = Expense::dropdown_expenses();
        $expense->date = sql2date($expense->date);

        if($expense->customer_id)
        {
            $data['customer_id_list'] = ($expense->customer_id) ? [ $expense->customer->id => $expense->customer->name] : [];
        }

        if($expense->project_id)
        {
            $data['project_id_list'] = ($expense->project_id) ? [ $expense->project->id => $expense->project->name] : [];
        }



        if($expense->attachment)
        {
            $expense->attachment_file_extension =  File::extension($expense->attachment);

            $expense->display_type = (in_array($expense->attachment_file_extension, ["png", "jpeg", "jpg", "bmp"])) ? 'image' : 'div';

            $expense->attachment_url = asset(Storage::url($expense->attachment));
        }


        if(!($expense->currency_id))
        {
            $expense->currency_id = config('constants.default_currency_id');
        }

        if($expense->tax_id)
        {
            $expense->tax_id = json_decode($expense->tax_id);
        }

        return view('expense.create', compact('data'))->with('rec', $expense);

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
        //
        $validator = Validator::make($request->all(), [
            'date'                          => ['required', new ValidDate()],
            'expense_category_id'           =>'required',
            'amount'                        => 'required',
            'name'                          => 'max:192',
            'note'                          => 'max:192',
            'attachment'                    => 'sometimes|required|max:1000|mimes:jpeg,bmp,png,doc,docx,pdf',
            'currency_id'                   =>'required',

        ]);

        if ($validator->fails()) {
            return  redirect()->back()
                ->withErrors($validator)
                ->withInput();
          
        }

        // Upload Attachment
        $attachment = NULL;

        if ($request->hasFile('attachment'))
        {
            $attachment = Storage::putFile('public/expense', $request->file('attachment') );
        }

        $amount                     = remove_commas($request->amount);

        $obj = Expense::find($id);
        $obj->expense_category_id   = $request->expense_category_id ;
        $obj->date                  = date2sql($request->date) ;
        $obj->amount                = remove_commas($request->amount) ;
        $obj->amount_after_tax      = Expense::calculate_amount_after_tax($request->tax_id, $amount);
        $obj->name                  = $request->name ;
        $obj->note                  = $request->note ;
        $obj->customer_id           = $request->customer_id ;
        $obj->vendor_id             = $request->vendor_id ;
        $obj->project_id            = $request->project_id ;
        $obj->is_billable           = ($request->is_billable) ? $request->is_billable : NULL;
        $obj->currency_id           = $request->currency_id;
        $obj->payment_mode_id       = $request->payment_mode_id ;
        $obj->reference             = $request->reference ;
        $obj->tax_id                = isset($request->tax_id) ? json_encode($request->tax_id) :  NULL;

        if($attachment)
        {
            $obj->attachment            = $attachment ;
        }
        elseif ($request->attachment_removed == 1)
        {
            $obj->attachment            = NULL ;
        }

        $obj->user_id               = auth()->id() ;
        $obj->save();

        // Log Activity   
        $description = sprintf(__('form.act_has_updated_an_expense_in'), anchor_link($obj->category->name, route('show_expense_page', $obj->id ) ));
        log_activity($obj, trim($description));



        session()->flash('message', __('form.success_update'));
        return  redirect()->route('expense_list');


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expense $expense)
    {

        // If the expnese was not invoiced the delete it
        if(!$expense->invoice_id)
        {           

            $value_to_save = __('form.category') . " : " . $expense->category->name . ", " .__('form.name') . " : " . $expense->name . ", " .__('form.amount') . " : " . $expense->amount;
            $expense->forcedelete();

            // Log Activity 
            $description    = sprintf(__('form.act_deleted'), __('form.expense'));                
            log_activity($expense, trim($description), $value_to_save ); 

            session()->flash('message', __('form.success_delete'));
        }
        else
        {
            session()->flash('message', __('form.expense_delete_not_allowed_msg'));
        }
        
        return  redirect()->route('expense_list');
    }

    function get_expense_details_ajax()
    {
        $id = Input::get('id');

        $rec = Expense::with(['category', 'customer', 'project', 'payment_mode'])->find($id);

        $rec->amount    = format_currency($rec->amount);
        $rec->amount_after_tax    = format_currency($rec->amount_after_tax);

        $rec->date      = sql2date($rec->date);

        if($rec->attachment)
        {
            $encrypted = Crypt::encryptString($rec->attachment);
            $rec->attachment_url = '<a href="'.route('download_receipt', $encrypted).'">'.__('form.download').'</a>';
        }

        $rec->customer_page_link = (isset($rec->customer) && $rec->customer) ? anchor_link($rec->customer->name, route('view_customer_page', $rec->customer->id)) : '';

        $rec->project_page_link = (isset($rec->project) && $rec->project) ? anchor_link($rec->project->name, route('show_project_page', $rec->project->id)) : '';



        if($rec->tax_id)
        {
            $taxes = json_decode($rec->tax_id);

            foreach ($taxes as $display_as)
            {
                $parsed_tax_string = parse_tax_string($display_as);
                $tax_information[] = $parsed_tax_string['rate']. '% ('. $parsed_tax_string['name'] .')';
            }

            $rec->tax_information = implode(", ", $tax_information);
        }

        return response()->json(
            array(
                'status'    => 1,
                'records'   => $rec,

            )
        );
    }

    function download_attachment($filename)
    {

        try {
            $file = Crypt::decryptString($filename);

            return Storage::download($file);

        } catch (DecryptException $e) {
            //
            abort(404);
        }
    }
}
