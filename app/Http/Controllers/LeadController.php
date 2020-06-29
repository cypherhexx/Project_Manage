<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\LeadStatus;
use App\LeadSource;
use App\Lead;
use App\Country;
use App\Tag;
use App\Note;

use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class LeadController extends Controller {

    /**
    * Display a listing of the resource.
    *
    * @return  \Illuminate\Http\Response
    */
    function index()
    {
        $data                   = lead::dropdown_for_filtering() + lead::statistics();

       $data['default_lead_status_id_list'] = array_keys($data['lead_status_id_list']);


       // Remove customer from the selected statuses
       unset($data['default_lead_status_id_list'][LEAD_STATUS_CUSTOMER]);
     
        return view('lead.index', compact('data'));
    }


    function paginate()
    {
        $query_key                  = Input::get('search');
        $search_key                 = $query_key['value'];

        $status_id                  = Input::get('status_id');
        $source_id                  = Input::get('source_id');
        $assigned_to                = Input::get('assigned_to');
        $additional_filter          = Input::get('additional_filter');

        $q                          = Lead::query();
        $query                      = Lead::orderBy('id', 'DESC')->with(['tags', 'assigned', 'status', 'source']);

        // If the user has permission to view only the leads that are assigned to him or created by himself;
        if(!check_perm('leads_view') && check_perm('leads_view_own'))
        {
            $q->where(function($k){
                $k->where('created_by', auth()->user()->id)->orWhere('assigned_to', auth()->user()->id);
            });

            $query->where(function($k){
                $k->where('created_by', auth()->user()->id)->orWhere('assigned_to', auth()->user()->id);
            });          
            
        }
        
        // Filtering Data                        
        if($status_id)
        {
            //$q->whereIn('lead_status_id', $status_id );
            $query->whereIn('lead_status_id', $status_id );
        }

        if($source_id)
        {
            //$q->whereIn('lead_source_id', $source_id );
            $query->whereIn('lead_source_id', $source_id );
        }
        
        if($assigned_to)
        {   
            if($assigned_to == 'unassigned')
            {
                //$q->whereNull('assigned_to');
                $query->whereNull('assigned_to');
            }
            else
            {
                //$q->whereIn('assigned_to', $assigned_to );
                $query->where('assigned_to', $assigned_to );
            }
            
        }
        if($additional_filter)
        {
            if($additional_filter == 'lost')
            {
                $q->whereNotNull('is_lost');
                $query->whereNotNull('is_lost');
            }
            if($additional_filter == 'junk')
            {
                $q->onlyTrashed();
                $query->onlyTrashed();
            }
            if($additional_filter == 'contacted_today')
            {
                // $q->whereDate('created_at', Carbon::today());
                // $query->whereDate('created_at', Carbon::today());
            }
            if($additional_filter =='created_today')
            {
                $q->whereDate('created_at', Carbon::today());
                $query->whereDate('created_at', Carbon::today());
            }
            if($additional_filter =='important')
            {
                $q->where('is_important', TRUE);
                $query->where('is_important', TRUE);
            }

            
        }
        else
        {
            $q->whereNull('is_lost');
                $query->whereNull('is_lost');
        }
        // Filtering Data




        


        $number_of_records  = $q->get()->count();


        if($search_key)
        {
            $query->where('first_name', 'like', $search_key.'%')
                ->orWhere('last_name', 'like', $search_key.'%')
                ->orWhere('company', 'like', $search_key.'%')
                ->orWhere('email', 'like', $search_key.'%')
                ->orWhere('phone', 'like', $search_key.'%')
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
                $is_lost = "";
                if($row->is_lost)
                {
                    $is_lost = '<br><span class="badge badge-danger">'. __('form.lost').'</small>';
                }

                $rec[] = array(
                    a_links(anchor_link($row->first_name . " " .$row->last_name.$is_lost, route('show_lead_page', $row->id) ), [

                        [
                            'action_link' => route('edit_lead_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission' => 'leads_edit',
                        ],
                        [
                            'action_link' => route('delete_lead', $row->id), 
                            'action_text' => __('form.delete'), 'action_class' => 'delete_item',
                            'permission' => 'leads_delete',
                        ]
                    ]),
                    $row->company,
                    $row->email,
                    $row->phone,
                    $row->get_tags_as_badges(true),
                    (isset($row->assigned))? anchor_link($row->assigned->first_name . " ". $row->assigned->last_name, route('member_profile', $row->assigned->id)) : '',
                    $row->status->name,
                    (isset($row->source)) ? $row->source->name : '',
                    ($row->last_contacted) ? Carbon::parse($row->last_contacted)->format("d-m-Y h:i A") : ''  ,

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
    public function create()
    {
        
        $data = Lead::dropdown();
                
        return view('lead.create', compact('data'))->with('rec', array());
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
            'lead_status_id' => 'required',
            'lead_source_id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email'     => 'nullable|email|unique:leads'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        // Saving Data
        $request['created_by']      = auth()->user()->id;        
        $obj                        = Lead::create($request->all());     
        $obj->tag_attach($request->tag_id);

        session()->flash('message', __('form.success_add'));
        return redirect()->route('leads_list');
    }

    /**
    * Display the specified resource.
    *
    * @param    int  $id
    * @return  \Illuminate\Http\Response
    */
    public function show($lead_id)
    {
        $lead = Lead::withTrashed()->where('id', $lead_id)->get();

        $data['touch_mediums'] = [
            __('form.phone_call')       => __('form.phone_call'),            
            __('form.email')            => __('form.email'),
           __('form.meeting')           => __('form.meeting'),
            __('form.social_network')   => __('form.social_network'),
            __('form.others')           => __('form.others'),
        ];

        $data['resolutions'] = [
            __('form.no_resolution')    => __('form.no_resolution'),            
            __('form.successful')       => __('form.successful'),
            __('form.unsuccessful')     => __('form.unsuccessful'),
            __('form.abondoned')        => __('form.abondoned'),
           __('form.left_voicemail')    => __('form.left_voicemail'),
        ];



        for($hours=0; $hours<24; $hours++)
        {
            for($mins=0; $mins<60; $mins+=30)
            { 
                $time = str_pad($hours,2,'0',STR_PAD_LEFT).':'.str_pad($mins,2,'0',STR_PAD_LEFT);
                $time = new \DateTime($time) ;
                $time = $time->format('h:i A');
                $data['time'][$time] = $time;
            }
        }
       

        if(count($lead) > 0)
        {
            $lead = $lead->first();
        }
        else
        {
            abort(404);
        }

        return view('lead.main', compact('data'))->with('rec', $lead);
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param    int  $id
    * @return  \Illuminate\Http\Response
    */
    public function edit($lead_id)
    {
        $lead = Lead::withTrashed()->find($lead_id);
     
        if($lead)
        {
            $data                   = Lead::dropdown();
            $lead['tag_id']         = $lead->tags()->pluck('tag_id')->toArray();
            return view('lead.create', compact('data'))->with('rec', $lead);
        }
        else
        {
            abort(404);
        }
        
    }



    /**
    * Update the specified resource in storage.
    *
    * @param    \Illuminate\Http\Request  $request
    * @param    int  $id
    * @return  \Illuminate\Http\Response
    */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
                'lead_status_id'   => 'required',
                'lead_source_id'   => 'required',
                'first_name' => 'required',
                'last_name' => 'required',          
                'email' => [
                    'nullable',
                    'email',
                    Rule::unique('leads')->ignore($id),
                    ],
                ]);

        if ($validator->fails()) {
        return  redirect()->back()
        ->withErrors($validator)
        ->withInput();
        }
        // Saving Data
        $obj = Lead::withTrashed()->find($id);

        if($obj)
        {
            $obj->update($request->all());  

            $obj->tag_sync($request->tag_id);

            session()->flash('message', __('form.success_update'));
            return  redirect()->route('leads_list');
        }
        else
        {
            abort(404);
        }


    }

    /**
    * Remove the specified resource from storage.
    *
    * @param    int  $id
    * @return  \Illuminate\Http\Response
    */
    public function destroy($lead_id)
    {

        $lead = Lead::withTrashed()->find($lead_id);

        if($lead)
        {
            $lead->delete();
            session()->flash('message', __('form.success_delete'));
            return  redirect()->route('leads_list');
        }       
        
    }

    public function log_touch(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'medium'            =>  'required',
            'date'              =>  'required|date_format:d-m-Y',
            'time'              =>  'required|date_format:h:i A',
            'resolution'        =>  'required',

        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }

        $lead = Lead::withTrashed()->find($id);
        // Saving Data
        $str = "";
        $str .= __('form.lead') . " : ". anchor_link($lead->first_name . " " . $lead->last_name, route('show_lead_page', $lead->id ));
        $str .= "<br><br>";
        $str .= __('form.medium') . " : ". Input::get('medium');
        $str .= "&nbsp, ";
        $str .= __('form.date') . " : ". Input::get('date');
        $str .= "&nbsp, ";
        $str .= __('form.time') . " : ". Input::get('time');
        $str .= "<br>";
        $str .= __('form.resolution') . " : ". Input::get('resolution');
        $str .= "<br>";
        $str .= __('form.description') ;
        $str .= "<br>";
        $str .= Input::get('description');
   
        $current_date  = \DateTime::createFromFormat('d-m-Y h:i A', $request->date . " ". $request->time);

        if($lead->last_contacted)
        {

            $previous_date = new \DateTime($lead->last_contacted);           

            if($current_date > $previous_date)
            {
                // Update
                $lead->last_contacted       = $current_date->format('Y-m-d H:i:s');
                $lead->last_contacted_by    = auth()->user()->id;
                $lead->save();
            }
        }
        else
        {
            $lead->last_contacted       = $current_date->format('Y-m-d H:i:s');
            $lead->last_contacted_by    = auth()->user()->id;
            $lead->save();
        }
        
        $description = __('form.entered_log_touch_for_lead');

        $log_name = activity_log_name_by_componet_id(COMPONENT_TYPE_LEAD).$lead->id;
        log_activity($lead, trim($description), $str, $log_name);

        return response()->json(['status' => 1]);
    }

    public function mark_as_important(Request $request, $id)
    {
        $lead = Lead::withTrashed()->find($id);

        if($lead)
        {
            $lead->is_important = $request->is_important;
            $lead->save();

            $msg = ($request->is_important) ? __('form.marked_as_important') : __('form.unmarked_as_important');
            return response()->json(['status' => 1, 'msg' => $msg ]);    
        }
        
        
    }


    public function save_social_link(Request $request, $id)
    {      
       $validator = Validator::make($request->all(), [
            'name'            =>  'required',
            'link'              =>  'required'           

        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }
            
        $lead = Lead::withTrashed()->find($id);
        
        if($lead)
        {
            if($lead->social_links)
            {
                $social_links = json_decode($lead->social_links, TRUE);
            }

            $social_links[$request->name] = add_http($request->link);

            $lead->social_links = json_encode($social_links);
            $lead->save();
            return response()->json(['status' => 1, 'data' => $social_links]);
        }     
        
    }



    function remove_social_link(Request $request, $id)
    {
        $lead = Lead::withTrashed()->find($id);

        if($lead)
        {
            if($lead->social_links)
            {
                $social_links = json_decode($lead->social_links, TRUE);

                if(isset($social_links[$request->name]))
                {   
                    // Remove the social network
                    unset($social_links[$request->name]);
                    // Json Encode it and save
                    $lead->social_links = json_encode($social_links);
                    $lead->save();
                }
            }

        }
        
    }


    public function save_smart_summary(Request $request, $id)
    {      
       $validator = Validator::make($request->all(), [
            'name'            =>  'required',
            'description'     =>  'required'           

        ]);

        if ($validator->fails()) 
        {
            return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
        }
        
        $lead = Lead::withTrashed()->find($id);

        if($lead)
        {

            if($lead->smart_summary)
            {
                $smart_summary = json_decode($lead->smart_summary, TRUE);
            }

            $smart_summary[$request->name] = $request->description;

            $lead->smart_summary = json_encode($smart_summary);
            $lead->save();
            
            return response()->json(['status' => 1, 'data' => $smart_summary]);
        }
    }

    function remove_smart_summary(Request $request, $id)
    {
        $lead = Lead::withTrashed()->find($id);

        if($lead && $lead->smart_summary)
        {
            $smart_summary = json_decode($lead->smart_summary, TRUE);

            if(isset($smart_summary[$request->name]))
            {   
                // Remove the social network
                unset($smart_summary[$request->name]);
                // Json Encode it and save
                $lead->smart_summary = json_encode($smart_summary);
                $lead->save();
            }

        }
    }


    public function mark_as_junk($id)
    {
        $lead = Lead::withTrashed()->find($id);

        if($lead)
        {
            if($lead->deleted_at)
            {
                $lead->deleted_at = NULL;
                $lead->save();
            }
            else
            {
                $lead->delete();    
            }
            
            session()->flash('message', __('form.success_update'));
            return  redirect()->back();        
        }
    }

    public function mark_as_lost($id)
    {
        $lead = Lead::withTrashed()->find($id);

        if($lead)
        {
            $lead->is_lost = ($lead->is_lost) ? NULL : TRUE;         
            $lead->save();
            session()->flash('message', __('form.success_update'));
            return  redirect()->back();
        }
    }

    


    function add_note(Request $request, $lead_id)
    {
        $lead = Lead::withTrashed()->find($lead_id);

        if(!$lead)
        {
             abort(404);      
        }
       

        $validator = Validator::make($request->all(), [            
            'details'                   => 'required',             

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
            
            $note           = new Note();
            $note->body     = Input::get('details');
            $note->user_id  = auth()->user()->id;            
            $lead->notes()->save($note);

            DB::commit();
            $success = true;
        } 
        catch (\Exception  $e) {
            
            $success = false;
            DB::rollback();
        }

        if ($success)
        {
            
            session()->flash('message', __('form.success_submit'));

            return redirect()->back();
            
        } 
        else 
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->back();
        }
    }


    

    function import_page()
    {       
        $data = Lead::dropdown(['lead_status_id_list', 'lead_source_id_list', 'assigned_to_list']);

        return view('lead.import', compact('data'))->with('rec', "");
    }

    
    private function validate_lead_data($records)
    {
        $validator = Validator::make($records, [      
            'first_name'    => 'required',
            'last_name'     => 'required',
            'email'         => 'nullable|email|distinct|unique:leads',
        ]);

        if ($validator->fails()) 
        {
           return $validator->errors()->all() ;
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
            'lead_status_id'            => 'required',
            'lead_source_id'            => 'required',
        ], [
            'lead_status_id.required'   => sprintf(__('form.field_is_required'), __('form.lead_status')),
            'lead_source_id.required'   => sprintf(__('form.field_is_required'), __('form.lead_source')),
        ]);

        if ($validator->fails()) {
            return  redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }



        $column_sequence_list = Lead::column_sequence_for_import() ;

        

        // Upload the file to a temporary directory. We will remove the file later usig cron.
        $file = Storage::putFileAs(TEMPORARY_FOLDER_IN_STORAGE, $request->file('file'), time().".".$request->file('file')->extension() );  
        $path = storage_path('app/'.$file);

        $extension      = $request->file('file')->getClientOriginalExtension();

        $reader         = ($extension == 'csv') ? new Csv() : new Xlsx();
        
        // Load the file with phpspreadsheet reader
        $spreadsheet    = $reader->load($path);  

        // Get the first active work sheet
        $worksheet      = $spreadsheet->getActiveSheet();

        
        // Get the highest column from the column sequeunce array. It will return a letter like: M         
        $highest_column = max(array_keys($column_sequence_list));

        // Get the next letter after the highest letter of the sequence
        $next_column_after_highest = ++$highest_column; 
        if (strlen($next_column_after_highest) > 1) 
        {   // if you go beyond z or Z reset to a or A
            $next_column_after_highest = $next_column_after_highest[0];
        }

        //


        // Check if the number of columns in the file match with requirement
        if(strtolower($worksheet->getHighestColumn()) < $highest_column)
        {
            session()->flash('validation_errors', [__('form.number_of_columns_do_no_match')]);
            session()->flash('message', __('form.import_was_not_successfull'));
            return  redirect()->back();
        }   
       

        if($worksheet)
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

                    $error = $this->validate_lead_data($cells);

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

                            $cells['lead_status_id']    =  $request->lead_status_id;
                            $cells['lead_source_id']    =  $request->lead_source_id;
                            $cells['assigned_to']       =  $request->assigned_to;
                            $cells['created_by']        =  auth()->user()->id;
                            
                            

                            if($cells['country'])
                            {                       
                                $country = Country::firstOrCreate(['name' => ucfirst(trim($cells['country'])) ]);
                                $cells['country_id']    = $country->id;
                            }

                           // Create the Lead                          
                            $lead                      = Lead::create($cells);     
                            
                            // Creating the Tags
                            $tags_array_list = [];
                            
                            if($cells['tags'])
                            {                  
                                $array_of_tags = explode(",", $cells['tags']);

                                if(!empty($array_of_tags) && count($array_of_tags) > 0)
                                {
                                    foreach ($array_of_tags as $value) 
                                    {
                                        $tag = Tag::firstOrCreate(['name' => $value ]);
                                        $tags_array_list[] =  $tag->id;
                                    }
                                }
                            }
                            // Attaching the tags
                            if(!empty($tags_array_list))
                            {
                               $lead->tag_attach($tags_array_list); 
                            }

                            // Remove the values of the Row
                            $this->clear_all_columns_of_a_row_in_spreadsheet($spreadsheet, $path, $column_sequence_list, $indexOfRow);

                            DB::commit();


                        } 
                        catch (\Exception  $e) 
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

 

    function download_sample_lead_import_file()
    {
        $filename = 'sample_lead_import_file';
        $spreadsheet = new Spreadsheet();  
        
        $Excel_writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $spreadsheet->setActiveSheetIndex(0);
        $activeSheet = $spreadsheet->getActiveSheet();

        $columns = Lead::column_sequence_for_import();

        foreach ($columns as $key=>$name) 
        {
            $activeSheet->setCellValue($key.'1' , str_replace("_", " ", ucfirst($name) ))->getStyle($key.'1')->getFont()->setBold(true);
        }
        

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); /*-- $filename is  Xlsx filename ---*/
        header('Cache-Control: max-age=0');
        $Excel_writer->save('php://output');
    }

    function report_page()
    {
        $data['months'] = [
            'january'                           => __('form.january'),
            'february'                          => __('form.february'),
            'march'                             => __('form.march'),
            'april'                             => __('form.april'),
            'may'                               => __('form.may'),
            'june'                              => __('form.june'),
            'july'                              => __('form.july'),
            'august'                            => __('form.august'),
            'september'                         => __('form.september'),
            'october'                           => __('form.october'),
            'november'                          => __('form.november'),
            'december'                          => __('form.december'),
        ];

        $data = $data + Lead::get_report_sources_conversion_for_graph();
        
        $data = $data + Lead::get_report_conversion_this_week_for_graph() + Lead::get_report_conversion_by_month_for_graph(date("F"));
    
        return view('lead.report', compact('data'));
    }

    function get_report_conversion_by_month_for_graph(Request $request)
    {
        $data = Lead::get_report_conversion_by_month_for_graph(Input::get('month'));
        return response()->json($data);
    }
}