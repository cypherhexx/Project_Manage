<?php

namespace App\Http\Controllers;

use App\Rules\ValidDateTime;
use App\TimeSheet;
use App\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Mpdf\Tag\Time;
use Pusher\Pusher;
use Notification;
use EmailReplyParser\Parser\EmailParser;

class TimeSheetController extends Controller
{   

    
    function paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $component_number   = Input::get('component_number');
        $component_id       = Input::get('component_id');
        $task_id            = Input::get('task_id');

        $q = TimeSheet::select('time_sheets.*', 'tasks.title AS task_title', 'users.first_name', 'users.last_name', 'tasks.is_billable', 'tasks.component_id')
        ->join('tasks', 'time_sheets.task_id' , '=', 'tasks.id')
        ->join('users', 'time_sheets.user_id' , '=', 'users.id')        
        ->orderBy('time_sheets.id', 'DESC')
        ;

        // If search from a component (example: Project)
        if($component_number && $component_id)
        {
            if($component_id == COMPONENT_TYPE_PROJECT)
            {
                $q->join('projects', 'tasks.component_number' , '=', 'projects.id')
                ->where('tasks.component_number', $component_number)
                ->where('tasks.component_id', $component_id)
                ->addSelect(DB::raw("projects.billing_type_id"));   
            }
            
        }
        elseif($task_id)
        {
            $q->where('time_sheets.task_id', $task_id);                
        }
        else
        {
            $output = array(
                "draw" => intval(Input::get('draw')),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            );

            return response()->json($output);
        }

        $query = $q;
        
        $number_of_records  =  $q->get()->count();        


        if($search_key)
        {
           $query->where('tasks.title', 'like', $search_key.'%')
                ->orwhere('users.first_name', 'like', $search_key.'%')
               ->orWhere('users.last_name', 'like', $search_key.'%')
               ;


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
                $start_time = Carbon::createFromFormat('Y-m-d H:i:s' , $row->start_time);
                $end_time   = Carbon::createFromFormat('Y-m-d H:i:s' , $row->end_time);

                if($row->user_id == auth()->user()->id || auth()->user()->is_administrator )
                {
                    $action = '<a class="edit_item btn btn-light btn-sm" data-id="'.$row->id.'" href="#"><i class="far fa-edit"></i></a> '.
                '<a class="delete_item btn btn-danger btn-sm" href="' . route('delete_time_sheet', $row->id) . '"><i class="far fa-trash-alt"></i></a>';
                }
                else
                {
                    $action = '';
                }
                

                //$duration = $end_time->diff($start_time)->format('%H:%I');

                $is_billed = "";

                if(($row->billing_type_id == BILLING_TYPE_TASK_HOURS) || ($row->is_billable))
                {
                    if($row->invoice_id)
                    {
                        $is_billed = '<a href="'. route('invoice_link', $row->invoice_id) .'"><span class="badge badge-success">' .__('form.invoiced') . '</span></a>';
                    }
                    else
                    {
                        $is_billed =  '<span class="badge badge-warning">' . __('form.not_billed') . '</span>';
                    }

                }
                
                $display_is_billed_beside_duration = ($task_id) ? "<br>". $is_billed : "";
            
                $rec[] = array(
                    anchor_link($row->first_name . " ". $row->last_name, route('member_profile', $row->user_id)),
                    anchor_link($row->task_title, route('show_task_page', $row->task_id)) . "<br>". $is_billed,
                    $start_time->format("d-m-Y h:i a"),
                    $end_time->format("d-m-Y h:i a"),
                    $row->note,
                    $row->duration,
                    time_to_decimal($row->duration) . $display_is_billed_beside_duration,
                    ($row->invoice_id) ? '' : $action

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


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'start_time'        => ['required', new ValidDateTime()],
            'end_time'          => ['required', new ValidDateTime(), function ($attribute, $value, $fail) use($request) {
                    
                    // validating if the start time and end time is valid
                    $start_time = new \DateTime($request->start_time);
                    $end_time   = new \DateTime($value);

                    if($start_time > $end_time || $start_time == $end_time)
                    {
                        $fail(__('form.invalid_end_time'));
                    }
                }
            ],
            'task_id'           => 'required',
            'user_id'           => 'required',
            'note'              => 'max:192',

        ], [
            'user_id.required'  => sprintf(__('form.field_is_required'), __('form.member')),

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        $start_time = Carbon::createFromFormat('d-m-Y h:i A' , $request->start_time);
        $end_time   = Carbon::createFromFormat('d-m-Y h:i A' , $request->end_time);

       

        // Saving Data
        $obj = new TimeSheet();
        $obj->task_id           = $request->task_id;
        $obj->user_id           = $request->user_id;
        $obj->start_time        = $start_time->format("Y-m-d H:i:s") ;
        $obj->end_time          = $end_time->format("Y-m-d H:i:s") ;
        $obj->duration          = $end_time->diff($start_time)->format('%H:%I');
        $obj->note              = $request->note;
        $obj->save();


        // Log Activity
        $task = Task::find($obj->task_id);
        $description = sprintf(__('form.act_has_logged_time'),         
            $obj->duration,
            anchor_link($task->title , route('show_task_page', $task->id ))

             );
        log_activity($obj, trim($description)); 

        return response()->json(['status' => 1]);

    }


    public function edit(Request $request)
    {
        $timesheet = TimeSheet::find(Input::get('timesheet_id'));

        if($timesheet)
        {
            $timesheet->start_time  = Carbon::createFromFormat( "Y-m-d H:i:s" , $timesheet->start_time)->format( 'd-m-Y h:i A') ;
            $timesheet->end_time    = Carbon::createFromFormat( "Y-m-d H:i:s" , $timesheet->end_time)->format( 'd-m-Y h:i A') ;

            return response()->json(['status' => 1, 'data' => $timesheet->toArray()]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }

    }

    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id'                => 'required',
            'start_time'        => ['required', new ValidDateTime()],
            'end_time'          => ['required', new ValidDateTime()],
            'task_id'           => 'required',
            'user_id'           => 'required',
            'note'              => 'max:192',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        $start_time = Carbon::createFromFormat('d-m-Y h:i A' , $request->start_time);
        $end_time   = Carbon::createFromFormat('d-m-Y h:i A' , $request->end_time);


        // Saving Data
        $obj = TimeSheet::find($request->id);

        
        $old_duration           = date("H:i", strtotime($obj->duration));

        $obj->task_id           = $request->task_id;
        $obj->user_id           = $request->user_id;
        $obj->start_time        = $start_time->format("Y-m-d H:i:s") ;
        $obj->end_time          = $end_time->format("Y-m-d H:i:s") ;
        $obj->duration          = $end_time->diff($start_time)->format('%H:%I');
        $obj->note              = $request->note;
        $obj->save();

        $new_duration           = $obj->duration;
        
        // Log Activity
        $task = Task::find($obj->task_id);   
        $description = sprintf(__('form.act_has_updated_logged_time'),          
            anchor_link($task->title , route('show_task_page', $task->id )),
            $old_duration,
            $new_duration
            
        );
        log_activity($obj, trim($description)); 

        return response()->json(['status' => 1]);

    }

    function destroy(TimeSheet $time_sheet)
    {
        $duration   = date("H:i", strtotime($time_sheet->duration));
        $task = Task::find($time_sheet->task_id);
        $time_sheet->delete();


         // Log Activity   
        $description = sprintf(__('form.act_has_deleted_logged_time'), $duration, anchor_link( $task->title , route('show_task_page', $task->id )));     
        log_activity($time_sheet, trim($description)); 

        session()->flash('message', __('form.success_delete'));
        return redirect()->back();
        
    }


    function report_page()
    {
        $data = TimeSheet::dropdown_for_filtering();

        return view('timesheet.report', compact('data'));
    }


    function report_paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $team_member_id     = Input::get('team_member_id');
        $customer_id        = Input::get('customer_id');
        $project_id         = Input::get('project_id');
        $date_range         = Input::get('date_range');
        

   


        $q = TimeSheet::select('time_sheets.*', 'tasks.title AS task_title', 'users.first_name', 'users.last_name', 'tasks.is_billable', 'tasks.component_id')
        ->join('tasks', 'time_sheets.task_id' , '=', 'tasks.id')
        ->join('users', 'time_sheets.user_id' , '=', 'users.id')        
        ->orderBy('time_sheets.id', 'DESC')
        ;

        if($date_range)
        {
            $date_range = get_date_from_range($date_range);

            $date_from = Carbon::parse( $date_range['date_from'] )->startOfDay();
            $date_to = Carbon::parse($date_range['date_to'])->endOfDay();

            $q->whereBetween('start_time', [ $date_from , $date_to ]);
        }

        if($team_member_id)
        {
            $q->where('user_id', $team_member_id);
        }

        

    
        if($project_id)
        {
            $q->join('projects', 'tasks.component_number' , '=', 'projects.id')
                ->where('tasks.component_number', $project_id)
                ->where('tasks.component_id', COMPONENT_TYPE_PROJECT)
                ->addSelect(DB::raw("projects.billing_type_id"));   
            
        }
        elseif($customer_id)
        {
            $rawSql = "tasks.id IN ( SELECT tasks.id FROM tasks 
                WHERE (component_id = ".COMPONENT_TYPE_CUSTOMER." AND component_number = ".$customer_id." ) 
                OR 
                ( tasks.component_number IN ( SELECT id FROM projects WHERE customer_id = ".$customer_id." ) AND 
                    component_id = ".COMPONENT_TYPE_PROJECT.")
                )";

                
                
                // $rawSql = "tasks.id IN ( SELECT tasks.id FROM tasks 
                // WHERE tasks.id IN (
                // SELECT id FROM projects WHERE customer_id = 6 ) AND component_id = 1

                
                // )";

            $q->whereRaw($rawSql);
           
        }
 

        $query = $q;
        
        $number_of_records  =  $q->get()->count();        


        if($search_key)
        {
           $query->where('tasks.title', 'like', $search_key.'%')
                ->orwhere('users.first_name', 'like', $search_key.'%')
               ->orWhere('users.last_name', 'like', $search_key.'%')
               ;


        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();
//

        $rec = [];

        if (count($data) > 0)
        {   
            $total = 0;

            foreach ($data as $key => $row)
            {
                $start_time = Carbon::createFromFormat('Y-m-d H:i:s' , $row->start_time);
                $end_time   = Carbon::createFromFormat('Y-m-d H:i:s' , $row->end_time);

                if($row->user_id == auth()->user()->id || auth()->user()->is_administrator )
                {
                    $action = '<a class="edit_item btn btn-light btn-sm" data-id="'.$row->id.'" href="#"><i class="far fa-edit"></i></a> '.
                '<a class="delete_item btn btn-danger btn-sm" href="' . route('delete_time_sheet', $row->id) . '"><i class="far fa-trash-alt"></i></a>';
                }
                else
                {
                    $action = '';
                }
                

                //$duration = $end_time->diff($start_time)->format('%H:%I');

                $is_billed = "";

                if(($row->billing_type_id == BILLING_TYPE_TASK_HOURS) || ($row->is_billable  && !$row->component_id))
                {
                    if($row->invoice_id)
                    {
                        $is_billed = '<a href="'. route('invoice_link', $row->invoice_id) .'"><span class="badge badge-success">' .__('form.invoiced') . '</span></a>';
                    }
                    else
                    {
                        $is_billed =  '<span class="badge badge-warning">' . __('form.not_billed') . '</span>';
                    }
                }
                
                $duration = time_to_decimal($row->duration);
                $rec[] = array(
                    anchor_link($row->first_name . " ". $row->last_name, route('member_profile', $row->user_id)),
                    anchor_link($row->task_title, route('show_task_page', $row->task_id)) . "<br>". $is_billed,
                    $start_time->format("d-m-Y h:i a"),
                    $end_time->format("d-m-Y h:i a"),
                    $row->note,
                    $row->duration,
                    time_to_decimal($row->duration),                   
                    ($row->invoice_id) ? '' : $action

                );

                $total              += $duration;

            }

            array_push($rec, [

                '<b>'. __('form.total_per_page'). '<b>',
                "",
                "",
                "",
                "",
                 "",
                '<b>'.$total. '<b>',                    
                "",             
                ""
               

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


}

