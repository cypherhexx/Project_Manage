<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Currency;
use App\Invoice;
use App\Expense;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller {

     /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {

        return view('currency.index');
    }


    function paginate()
    {

        $query_key = Input::get('search');
        $search_key        = $query_key['value'];
        $number_of_records = Currency::all()->count();


        $query = Currency::orderBy('id', 'DESC');


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
                $fa_check   = ($row->is_default) ?  '' : 'style="display:none"';
                $is_default = ($row->is_default) ? 'style="display:none"' :  '';

                $rec[] = array(
                    a_links('<a class="edit_item" data-id="'.$row->id.'" href="#">'.$row->code.'</a>' , []),
                    $row->symbol,
                    '<i class="fas fa-check" '.$fa_check.' ></i><input '.$is_default.' class="is_default_currency" data-id="'.$row->id.'"  type="radio" name="is_default" value="1">',
                    side_by_side_links($row->id, route('delete_currency', $row->id) )

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
        $validator = Validator::make($request->all(), [
            'code'          =>  'required|unique:currencies',
            'symbol'        =>  'required',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj = new Currency();
        $obj->code              = $request->code;
        $obj->symbol            = $request->symbol;
        
        $obj->save();

        return response()->json(['status' => 1]);
    }

    public function edit(Request $request)
    {
        $obj = Currency::find(Input::get('id'));

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

        $validator = Validator::make($request->all(), [
            'symbol'                =>  'required',
            'code'                  => [
                                            'required',
                                            Rule::unique('currencies')->ignore($request->id),
                                    ],
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        // Saving Data
        $obj                    = Currency::find($request->id);
        $obj->code              = $request->code;
        $obj->symbol            = $request->symbol;
        $obj->save();

        return response()->json(['status' => 1]);

    }

    function destroy(Currency $currency)
    {
        try {
            $currency->delete();
            session()->flash('message', __('form.success_delete'));

        } catch (\Illuminate\Database\QueryException $e) {
           // Handle Integrity constraint violation
            
            session()->flash('message', __('form.delete_not_possible_fk'));
        }

        
        return redirect()->back();
    }


    function change_default_currency(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                =>  'required',
            
        ], [
            'id.required'       => __('form.please_select_a_currency')
        ]);

        if ($validator->fails()) 
        {        

            session()->flash('error_message',  $validator->errors()->all()[0] );
            return  redirect()->back();
        }

        $id = Input::get('id');

        if($id)
        {   
            $c = Currency::where('is_default', TRUE)->get();

            if(count($c) > 0)
            {
                $current_default_currency_id = $c->first()->id;           

                $number_of_times_used_invoice = Invoice::where('currency_id', $current_default_currency_id )->get()->count();
      
                $number_of_times_used_expense = Expense::where('currency_id', $current_default_currency_id )->get()->count();

                $number_of_times_used         = $number_of_times_used_invoice + $number_of_times_used_expense;

                if($number_of_times_used > 0)
                {
                    session()->flash('error_message', __('form.msg_changing_currency_is_not_possble'));
                    return  redirect()->back();
                }

            }

            

            DB::beginTransaction();
            $success = false;

            try {           

                $table              = (new Currency)->getTable();
                DB::table($table)->update(['is_default'=> NULL]);
                
                $obj                = Currency::find(Input::get('id'));
                $obj->is_default    = TRUE;
                $obj->save();

                DB::commit();
                $success = true;

            } catch (\Exception  $e) {

                $success = false;

                DB::rollback();
                

            }

            if($success)
            {
                session()->flash('message', __('form.successfully_changed_the_default_currency'));
                return  redirect()->back();
            }
            
        }

       session()->flash('error_message', __('form.could_not_perform_the_requested_action'));
       return  redirect()->back();
    }

}