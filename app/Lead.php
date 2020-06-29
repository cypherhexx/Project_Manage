<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Lead extends Model {

    use SoftDeletes;
    use \App\Traits\TagOperation;

    protected $fillable = ['lead_status_id', 'lead_source_id', 'assigned_to', 'first_name' , 'last_name', 'position', 'email', 'website', 'phone', 'company', 'is_important', 'address', 'city', 'state', 'zip_code', 'country_id', 'description', 'created_by', 'social_links' ,'smart_summary', 'photo'];

    protected $dates = ['deleted_at'];

    function assigned()
    {
        return $this->belongsTo(User::class ,'assigned_to','id')->withTrashed();
    }

    function person_last_contacted()
    {
        return $this->belongsTo(User::class ,'last_contacted_by','id')->withTrashed();
    }

    function notes()
    {
        return $this->morphMany(Note::class, 'noteable')->orderBy('id', 'DESC');
    }


    public function reminders()
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }



    function country()
    {
        return $this->belongsTo(Country::class ,'country_id','id');
    }


    function status()
    {
        return $this->belongsTo(LeadStatus::class ,'lead_status_id','id');
    }


    function source()
    {
        return $this->belongsTo(LeadSource::class ,'lead_source_id','id');
    }
    

    static function sales_agent_dropdown()
    {        

        return User::activeUsers()
                ->select(
                    DB::raw("CONCAT(first_name,' ',last_name) AS name"),'id')->pluck('name', 'id')->toArray();
    }

    static function dropdown_for_filtering()
    {
        $data['lead_status_id_list'] = LeadStatus::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        $data['lead_source_id_list'] = LeadSource::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        $data['assigned_to_list']    = array('' => __('form.all')) + array('unassigned' => __('form.not_assigned')) +  self::sales_agent_dropdown();

        $data['additional_filter_list'] = [
            ''                      => __('form.none'),
            'important'             => __('form.important'), 
            'lost'                  => __('form.lost'), 
            'junk'                  => __('form.junk'), 
            'contacted_today'       => __('form.contacted_today'),
            'created_today'         => __('form.created_today'),

        ];

        return $data;

    }

    static function dropdown($required = NULL)
    {
        $select = __('form.dropdown_select_text');
        $data = [];



        if(!$required || in_array('lead_status_id_list', $required))
        {
             $data['lead_status_id_list'] = array('' => $select) + LeadStatus::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        }

       
        if(!$required || in_array('lead_source_id_list', $required) )
        {
            $data['lead_source_id_list'] = array('' => $select) + LeadSource::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        }

        if(!$required || in_array('assigned_to_list', $required) )
        {
            $data['assigned_to_list'] = array('' => $select) + self::sales_agent_dropdown();
        }

        if(!$required || in_array('tag_id_list', $required) )
        {

            $data['tag_id_list'] = Tag::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        }
  
        

        if(!$required || in_array('country_id_list', $required) )
        {
            $data['country_id_list'] = array('' => $select) + Country::orderBy('name','ASC')->pluck('name', 'id')->toArray();
        }

        

        

        return $data;
    }


    static function statistics()
    {
        $data['lost_lead']      = 0;
        $data['junk_lead']      = 0;
        $data['stat']           = [];
        $data['stat_customer']  = ['name' => __('form.customer'), 'total' => 0];
        $extraWhere             = "";
        $leads                  = Lead::select('lead_status_id', DB::raw('count(*) as total'))->groupBy('lead_status_id')
                                    ->whereNull('is_lost');
        

        if(!check_perm('leads_view') && check_perm('leads_view_own'))
        {
            $extraWhere = "AND (created_by = ". auth()->user()->id ." OR assigned_to = ". auth()->user()->id .")";          

            $leads->where('created_by', auth()->user()->id)->orWhere('assigned_to', auth()->user()->id);  
        }


        
        $sql = "SELECT 'lost' AS name , COUNT(id) AS total FROM leads WHERE is_lost = 1 $extraWhere
                UNION ALL
                SELECT 'junk' AS name , COUNT(id) AS total FROM leads WHERE deleted_at IS NOT NULL $extraWhere
                UNION ALL 
                SELECT 'active' AS name , COUNT(id) AS total FROM leads WHERE deleted_at IS NULL AND is_lost IS NULL $extraWhere";

       $record = DB::select($sql);

          
       foreach ($record as $row) 
       {
            $lost_and_junk_data[$row->name] = $row->total;
       }

       // Calculate total number of leads
       $total_num_of_leads = array_sum($lost_and_junk_data);

       if($total_num_of_leads > 0)
       {
            // Calculate Percentage
           $data['lost_lead'] = round(($lost_and_junk_data['lost']/$total_num_of_leads) * 100);
           $data['junk_lead'] = round(($lost_and_junk_data['junk']/$total_num_of_leads) * 100);
       }
           
        $leads          = $leads->pluck('total','lead_status_id')->all();         

        $lead_statuses  = LeadStatus::all();     

        if(count($lead_statuses) > 0)
        {
            foreach ($lead_statuses as $status) 
            {
                $ls[$status->id] = ['name' => $status->name, 'total' => (isset($leads[$status->id])) ? $leads[$status->id] : 0  ];
            }

                       
            $stat_customer = $ls[LEAD_STATUS_CUSTOMER];
            // Remove Customer 
            unset($ls[LEAD_STATUS_CUSTOMER]);
            $data['stat']           = $ls;
            $data['stat_customer']  = $stat_customer;
        }    

        

        return $data;
            
    }

    static function home_page_stat()
    {
        $data['percent']    = 0;
        $data['figure']     = '0 / 0';

        $leads = Lead::select('lead_status_id', DB::raw('count(*) as total'))
        ->groupBy('lead_status_id')->whereNull('is_lost')->pluck('total','lead_status_id')->all(); 
        

        if(count($leads) > 0)
        {
            $total_number_of_leads                  = array_sum($leads);
            $number_of_leads_converted_to_customer  = (isset($leads[LEAD_STATUS_CUSTOMER])) ? $leads[LEAD_STATUS_CUSTOMER] : 0;
            $data['figure']                         = $number_of_leads_converted_to_customer . " / ". $total_number_of_leads;
            $data['percent']                        = round(($number_of_leads_converted_to_customer/$total_number_of_leads) * 100);
           
            
        }
        return $data;
    }

    static function column_sequence_for_import()
    {
        $columns = ['first_name', 'last_name', 'position', 'company', 
           'description', 'country', 'zip_code', 
           'city', 'state', 'address', 'email', 
           'website', 'phone', 'tags'
        ];

        $alphas = range('A', 'Z');  

        foreach ($columns as $key => $value) 
        {
           $columns[$alphas[$key]] = $value;
           unset($columns[$key]);
        }

        return $columns;
    }


    public static function get_report_sources_conversion_for_graph()
    {
        $leads = Lead::with(['source'])->select('lead_source_id', DB::raw('count(*) as total'))->groupBy('lead_source_id')->get();                 
                        

        $data['sources_conversion']['labels'] = [];
        $data['sources_conversion']['data'] = [];

        if(count($leads) > 0)
        {
            foreach ($leads as $key => $row) 
            {
                $data['sources_conversion']['labels'][] = $row->source->name;
                $data['sources_conversion']['data'][] = $row->total;
                
            }      
        }

        return $data;
    }

    public static function get_report_conversion_this_week_for_graph()
    {
        $day = (int) (config('constants.first_day_of_week')) ?? 1;
     
       Carbon::setWeekStartsAt($day);
       Carbon::setWeekEndsAt(abs( 6 - ((6 - $day) + 1) ));
       $now = Carbon::now();

       $start_date  = $now->startOfWeek()->format('Y-m-d H:i:s');
       $end_date    = $now->endOfWeek()->format('Y-m-d H:i:s');
       
   
       $records = Lead::select([DB::raw('count(id) as count'), DB::raw('DATE(created_at) as day')])
                    ->groupBy('day')->whereBetween('created_at', [$start_date , $end_date ])->get();

      
        $data['conversion_this_week']['labels'] = [
                      __('form.sunday'),
                      __('form.monday'),
                      __('form.tueday'),
                      __('form.wednesday'),
                      __('form.thursday'),
                      __('form.friday'),
                      __('form.saturday'),
                  ];
      
        $data['conversion_this_week']['data'] = [
                'sunday'                   => 0,
                'monday'                   => 0,
                'tueday'                   => 0,
                'wednesday'                => 0,
                'thursday'                 => 0,
                'friday'                   => 0,
                'saturday'                 => 0,                    
                      
        ];   

       if(count($records) > 0)
       {

        foreach ($records as $key => $row) 
        {

             $data['conversion_this_week']['data'][strtolower(Carbon::createFromFormat('Y-m-d', $row->day)->format('l'))] = $row->count;           
        }

       }

       $data['conversion_this_week']['data'] = array_values($data['conversion_this_week']['data']);
       
       return $data;
    }


    public static function get_report_conversion_by_month_for_graph($month)
    {   


       $start_date = (new Carbon('first day of '.$month))->format('Y-m-d');
       $end_date = (new Carbon('last day of '.$month))->format("Y-m-d");


       $period = \Carbon\CarbonPeriod::create($start_date, $end_date);



        // Iterate over the period
        foreach ($period as $date) 
        {
            $data['conversion_by_month']['labels'][] = $date->format('Y-m-d');

            $data['conversion_by_month']['data'][$date->format('Y-m-d')] = 0;
        }



        $records = \App\Lead::select([DB::raw('count(id) as count'), DB::raw('DATE(created_at) as day')])
                    ->groupBy('day')->whereBetween('created_at', [$start_date , $end_date ])->get();

    

       if(count($records) > 0)
       {

        foreach ($records as $key => $row) 
        {

             $data['conversion_by_month']['data'][$row->day] = $row->count;           
        }

       }
       
       $data['conversion_by_month']['data'] = array_values($data['conversion_by_month']['data']);
       return $data;

    }
    
}