<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Notification;

use App\Setting;
use App\Ticket;
use App\PreDefinedReply;
use App\NumberGenerator;
use \Carbon\Carbon;
use App\Comment;
use App\Attachment;
use App\TicketNote;
use App\Project;
use App\User;
use App\Notifications\NewSupportRequest;
use App\Notifications\NewCommentOnTicket;



class TicketController extends Controller
{

    function index()
    {
        $data = [];
    	return view('customer_panel.support.index', compact('data'))->with('rec', []); 
    }

    function paginate()
    {
        $query_key              = Input::get('search');
        $search_key             = $query_key['value'];


        $q                      = Ticket::query();       

        $q->Where('customer_contact_id', auth()->user()->id );

        $query                  = $q->orderBy('id', 'DESC')
                                    ->with(['department', 'service', 'status', 'priority', 'tags']);       

        $number_of_records = $q->count();        


        if($search_key)
        {
            $query->where('number', 'like', $search_key.'%')
               ->orWhere('subject', 'like', $search_key.'%')
           
                ->orWhereHas('department', function ($q) use ($search_key) {
                    $q->where('departments.name', 'like', $search_key.'%');
                })

                // ->orWhereHas('service', function ($q) use ($search_key) {
                //     $q->where('ticket_services.name', 'like', $search_key.'%');
                // })


                ->orWhereHas('status', function ($q) use ($search_key) {
                    $q->where('ticket_statuses.name', 'like', $search_key.'%');
                })
           
            ;
        }

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();


        $rec = [];

        if (count($data) > 0)
        {
            foreach ($data as $key => $row)
            {              
                $rec[] = array(
                    anchor_link($row->number, route('cp_show_ticket_page', $row->id)),
                    $row->subject,
                    $row->department->name,
                    (isset($row->project->name)) ? anchor_link($row->project->name, route('cp_show_project_page', $row->project->id) ) : "",
                    (isset($row->service->name)) ? $row->service->name : "",
                   
                    $row->status->name,
                    $row->priority->name,
                    ($row->last_reply) ? Carbon::parse($row->last_reply)->format('d-M-Y H:i:s') : __('form.no_reply_yet'),
                    $row->created_at->format('d-M-Y H:i:s')

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

		$data = Ticket::customer_dropdown();     

 
        return view('customer_panel.support.create_ticket', compact('data'))->with('rec', []);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param    \Illuminate\Http\Request $request
     * @return  \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'subject'               => 'required',
            'department_id'        	=> 'required',            
            'details'				=> 'required',
            'ticket_priority_id'    => 'required',
            

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

			$request['number']                   = NumberGenerator::gen(COMPONENT_TYPE_TICKET);
			$request['created_by']               = auth()->user()->id;
			$request['user_type']                = USER_TYPE_CUSTOMER;

            $request['customer_contact_id']     = auth()->user()->id;
            $request['name']                    = auth()->user()->first_name . " " . auth()->user()->last_name;
            $request['email']                   = auth()->user()->email;
			$request['ticket_status_id']        = TICKET_STATUS_OPEN ;

			// Saving Data        
	        $ticket  = Ticket::create($request->all());     

	        $comment 			= new Comment();
	        $comment->body 		= Input::get('details');
	        $comment->user_id 	= auth()->user()->id;
	        $comment->user_type = USER_TYPE_CUSTOMER;
	        $ticket->comments()->save($comment);

	       

	        // Save the attachments (If exists)
            $files             = $request->attachment;
       
            if(!empty($files))
            {
                $attachment = new Attachment();
                $attachment->add($files, $comment);       
            
            }


            // Log Actitivy
            $description    = __('form.new_ticket_opened');
            $details        = anchor_link($ticket->number, route('show_ticket_page', $ticket->id ) );            
            log_activity($ticket, $description , $details); 


            // Send Notification to all Members of the department
            $ticket->notify_new_ticket_created_by_customer();
        


        	DB::commit();
            $success = true;
        } 
        catch (\Exception  $e) {
            
            $success = false;
            DB::rollback();

            
        }

        if ($success)
        {

            session()->flash('message', __('form.success_add'));
            return redirect()->route('cp_ticket_list');
        } 
        else 
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->back();
        }
    }


    

    function show(Ticket $ticket)
    {
    
    	$data = Ticket::customer_dropdown();
    	return view('customer_panel.support.show_ticket', compact('data'))->with('rec', $ticket);
    }

    


    function add_reply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                        => 'required',
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

              
            $ticket                     = Ticket::find(Input::get('id')); 

            // Change ticket status to open
            $ticket->ticket_status_id   = TICKET_STATUS_OPEN;
            $ticket->save();

            // Save Comment
            $comment                    = new Comment();
            $comment->body              = Input::get('details');
            $comment->user_id           = auth()->user()->id;
            $comment->user_type         = USER_TYPE_CUSTOMER;
            $ticket->comments()->save($comment);

            // Save the attachments (If exists)
            $files             = $request->attachment;
       
            if(!empty($files))
            {
                $attachment = new Attachment();
                $attachment->add($files, $comment);       
            
            }
            
            // Send notification to Responsible Team Members
            $ticket->notify_reply_from_customer($comment);          


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

            if($request->return_to_ticket_list)
            {
                redirect()->route('ticket_list');
            }
            else
            {
                return redirect()->back();
            }
            
        } 
        else 
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->back();
        }
    }


  
}
