<?php

namespace App\Http\Controllers;


use Auth;
use App\InvoiceStatus;
use App\Invoice;
use App\Expense;
use App\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use App\Services\Pdf;
use Spatie\Activitylog\Models\Activity;


class ReportController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function sales()
    { 
        $group_name = app('request')->input('group');
        $select     = __('form.dropdown_select_text');
        
        $data['currency_id_list']           =   Currency::orderBy('code','ASC')->pluck('code', 'id')->toArray();

        if($group_name == '')
        {
            $data['status_id_list']         =   InvoiceStatus::orderBy('name','ASC')->pluck('name', 'id')->toArray();
            $data['sales_agent_id_list']    =   Invoice::sales_agent_dropdown();
        }
        if($group_name == 'items')
        {
            
            $data['sales_agent_id_list']    =  Invoice::sales_agent_dropdown();
        }
        return view('reports.sales', compact('data'));
    }


    function expenses()
    { 
      
      // DB::select("SELECT SUM(amount_after_tax) AS total, expense_category_id, MONTH(date) AS month
      //   FROM expenses
      //   WHERE date BETWEEN '2018-01-01' AND '2018-10-04'
      //   GROUP BY expense_category_id, MONTH(date)
      //   ORDER BY month ASC");

        $system_starting_year = config('constants.system_starting_year');

        if(!$system_starting_year)
        {
            $system_starting_year = date("Y");
        }

        $years = range($system_starting_year, date("Y"));
        arsort($years);       

        foreach ($years as $year) 
        {
            $data['year_list'][$year] = $year;
        }

        $year = Input::get('year');

        if(!$year)
        {
            $year = date("Y");
        }

        $expense_categories = $this->generate_expense_report($year, Input::get('exclude_billable'));       

       
        return view('reports.expenses', compact('data'))->with('expenses', $expense_categories);


    }


    function generate_expense_report($year, $exclude_billable)
    {
        $expense_categories = \App\ExpenseCategory::select(['id', 'name'])->get();

        if(count($expense_categories) > 0)
        {
            $date_from = $year.'-01-01' ; $date_to = $year.'-12-31';

            $expenses = Expense::select(DB::raw("SUM(amount) AS total, SUM(amount_after_tax) AS amount_after_tax, expense_category_id, MONTH(date) AS month"))
            ->whereBetween('date', [$date_from, $date_to ])
            ->groupBy('expense_category_id', DB::raw("MONTH(date)") )
            ->orderBy('month', 'ASC');

            if($exclude_billable == 1)
            {
                $expenses->whereNull('is_billable');
            }
            $expenses = $expenses->get();

            if(count($expenses) > 0)
            {
                foreach ($expenses as $key=>$row) 
                {   

                    $expenses[$row->expense_category_id . "_". $row->month ] = $row;
                    unset($expenses[$key]);
                }

                $expenses = $expenses->toArray();
            }
            else
            {
                $expenses = [];
            }

            

            if(count($expense_categories) > 0)
            {
                foreach ($expense_categories as $key=>$expense_category) 
                {
                    
                    foreach (range(1, 12) as $month) 
                    {
                        $m      = $month;
                        $month  = 'month_'.$month;
                        $k      = $expense_category->id."_".$m ;

                        $total_tax[$month]  = (!isset($total_tax[$month])) ? 0 : $total_tax[$month];
                        $sub_total[$month]  = (!isset($sub_total[$month])) ? 0 : $sub_total[$month];
                        $total[$month]      = (!isset($total[$month])) ? 0 : $total[$month];

                       if(isset($expenses[$expense_category->id."_".$m] ))
                       {
                            $expense_categories[$key][$month] = $expenses[$k]['total'];

                            // Calculating Net Amount (Subtotal)                   
                            $sub_total[$month]  += $expenses[$k]['total'];

                            // Calculating Total Tax
                            $exp                = $expenses[$k];                   
                            $total_tax[$month]  += $exp['amount_after_tax'] - $exp['total']; 
                            
                            // Calculating Total
                            $total[$month]  += $exp['amount_after_tax']; 

                       }
                       else
                       {
                            $expense_categories[$key][$month]           = 0;
                            // Calculating Net Amount (Subtotal)  
                            $sub_total[$month]                          += 0;

                            // Calculating Total Tax
                            $total_tax[$month]                          += 0;

                            // Calculating Total
                            $total[$month]                              += 0;
                       }
                       

                       

                      
                    }
                    
                }
                $expense_categories = $expense_categories->toArray();
            }
           
        
           $sub_total['name']   = __('form.sub_total');
           $total_tax['name']   = __('form.total_tax');
           $total['name']       = __('form.total');

           array_push($expense_categories, $sub_total);
           array_push($expense_categories, $total_tax);
           array_push($expense_categories, $total);
        }

        return $expense_categories;
    }

    function download_expense_report(Request $request)
    {

        $year = $request->get('year');


        if(!$year)
        {
            $year = date("Y");
        }

        $expense_categories = $this->generate_expense_report($year, $request->get('exclude_billable'));

        $data['page_title'] = __('form.expense_report'). " ". $year ;

        $data['html'] = view('reports.pdf.expenses', compact('data') )->with('expenses', $expense_categories)->render();

        

        $html = view('layouts.print.template', compact('data'))->render();

        $file_name = str_replace(" ", "_", trim($data['page_title']));
        
        $pdf = new Pdf([
            'orientation' => 'L']);
        $pdf->download($html, $file_name);
    }


    function activity_log()
    {
        return view('reports.activity_log');
    }

    function activity_log_paginate()
    {
        $activity           = Activity::all();

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];  

        $number_of_records  = Activity::all()->count();
        $query              = Activity::orderBy('id', 'DESC');

      

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();
//

        $rec = [];

        if (count($data) > 0)
        {
            foreach ($data as $key => $row)
            {
                if(isset($row->causer_id) && !empty($row->causer_id))
                {
                    $causer = $row->causer()->withTrashed()->get()->first();

                    $rec[] = array(
                        \Carbon\Carbon::parse($row->created_at)->diffForHumans(),
                        anchor_link($causer->first_name . " " . $causer->last_name, route('member_profile', $causer->id)) ,
                        $row->description . " ". $row->getExtraProperty('item')
                        
                        

                    );
                }
                

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