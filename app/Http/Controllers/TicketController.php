<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Notification;
use App\Setting;
use App\Ticket;
use App\PreDefinedReply;
use App\NumberGenerator;
use \Carbon\Carbon;
use App\Comment;
use App\Attachment;
use App\Project;
use App\Note;
use App\Mail\NewTicketCreated;
use App\Notifications\NewCommentOnTicket;
use App\Mail\TicketReply;
use App\PotentialCustomer;
use App\CustomerContact;
use App\User;
use App\Notifications\TicketAssigned;
use App\Notifications\TicketUnAssigned;

class TicketController extends Controller
{

    function index()
    {
        $data           = Ticket::dropdown_for_filtering();
        $data['stat']   = Ticket::statistics();


   	    return view('support.index', compact('data'))->with('rec', []); 
    }

    function paginate()
    {
        $query_key              = Input::get('search');
        $search_key             = $query_key['value'];

        // Individual Page
        $current_id             = Input::get('current_id');
        $email                  = Input::get('email');
        $customer_contact_id    = Input::get('customer_contact_id');
        $project_id             = Input::get('project_id');

        // Filter
        $department_id          = Input::get('department_id');
        $ticket_status_id       = Input::get('ticket_status_id');
        $ticket_priority_id     = Input::get('ticket_priority_id');
        $assigned_to            = Input::get('assigned_to');
        $q                      = Ticket::query(); 

        // If the user has permission to view only the leads that are assigned to him or created by himself;
        if(!check_perm('tickets_view') && check_perm('tickets_view_own'))
        {
            $q->where(function($k){
                $k->where('created_by', auth()->user()->id)->orWhere('assigned_to', auth()->user()->id);
            });                 
            
        }

        if($project_id)
        {
            $q->Where('project_id', $project_id);
        }
       

        if($current_id)
        {
            $q->where('id', '<>', $current_id)->where('email', $email);
        }

        if($customer_contact_id)
        {
            $q->Where('customer_contact_id', $customer_contact_id);
        }
        $query                  = clone($q);

        $query                  = $query->orderBy('id', 'DESC')
                                    ->with(['department', 'service', 'status', 'priority', 'assigned_user']);

       // Data Filtering 
       if($department_id)
        {
           $query->whereIn('department_id', $department_id);
        }

        if($ticket_status_id)
        {
            $query->whereIn('ticket_status_id', $ticket_status_id);
        }

        if($ticket_priority_id)
        {
            $query->whereIn('ticket_priority_id', $ticket_priority_id);
        }
        if($assigned_to)
        {
            if($assigned_to == 'unassigned')
            {
                $query->whereNull('assigned_to');
            }
            else
            {
                $query->where('assigned_to', $assigned_to);
            }
            
        }
        // End of Data Filtering

        $number_of_records = $q->count();        


        if($search_key)
        {
            $query->where('number', 'like', $search_key.'%')
               ->orWhere('subject', 'like', $search_key.'%')
           
                ->orWhereHas('department', function ($q) use ($search_key) {
                    $q->where('departments.name', 'like', $search_key.'%');
                })

                ->orWhereHas('service', function ($q) use ($search_key) {
                    $q->where('ticket_services.name', 'like', $search_key.'%');
                })


                ->orWhereHas('status', function ($q) use ($search_key) {
                    $q->where('ticket_statuses.name', 'like', $search_key.'%');
                })
           
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
                if(isset($row->assigned_user->first_name))
               {
                    $assigned_to = anchor_link($row->assigned_user->first_name . " ". $row->assigned_user->last_name, route('member_profile', $row->assigned_user->id)) ;
               }
               else
               {
                 $assigned_to = "";
               }

                $rec[] = array(
                    // '<input type="checkbox" name="ids[]" value="'.$row->id.'">',
                    a_links(anchor_link($row->number, route('show_ticket_page', $row->id)), [
                        [
                            'action_link' => route('edit_ticket_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission' => 'tickets_edit',
                        ],
                        [
                            'action_link' => route('delete_ticket', $row->id), 
                            'action_text' => __('form.delete'), 'action_class' => 'delete_item', 
                            'permission' => 'tickets_delete'
                        ]
                    ]),
                    $row->subject,
                    $row->department->name,
                    (isset($row->service->name)) ? $row->service->name : "",
                    $row->name,
                    $row->status->name,
                    $row->priority->name,
                    $assigned_to,
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

		$data = Ticket::dropdown();

        $project_id     = app('request')->input('project_id');
        $rec            = new \stdClass();

        if($project_id)
        {
            if(!check_perm('projects_create'))
            {
                abort(404);
            }

            $rec                = new \stdClass();
            $project            = Project::with(['customer'])->find($project_id);

            if(!$project)
            {
                abort(404);
            }

            $rec->project_id                    = $project_id;
            $rec->name                          = $project->customer->primary_contact->first_name . " " . $project->customer->primary_contact->last_name ;
            $rec->email                         = $project->customer->primary_contact->email;
            $rec->customer_contact_id           = $project->customer->primary_contact->id;
            $data['customer_contact_id']        = [ $project->customer->primary_contact->id =>  $rec->name ] ;
            $data['project_id_list']            = [ $project->id =>  $project->name ] ;
        }

        $rec->assigned_to                       = auth()->user()->id;
        
        return view('support.create_ticket', compact('data'))->with('rec', $rec);
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
            'name'         			=> 'required',
            'email'             	=> 'required|email',
            'details'				=> 'required',
            'email_cc'              => 'nullable|email',
            'ticket_priority_id'    => 'required',
            'assigned_to'           => 'required'
            

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

			$request['number']       = NumberGenerator::gen(COMPONENT_TYPE_TICKET);
			$request['created_by']   = auth()->user()->id;
			$request['user_type']    = USER_TYPE_TEAM_MEMBER;
			
			// Saving Data        
	        $ticket                   = Ticket::create($request->all());     

	        $comment 			      = new Comment();
	        $comment->body 		      = Input::get('details');
	        $comment->user_id 	      = Auth::user()->id;
	        $comment->user_type       = USER_TYPE_TEAM_MEMBER;
	        $ticket->comments()->save($comment);

	        // Tags
	        $ticket->tag_attach($request->tag_id);


            // If the customer contact id is empty
            if(!$request->customer_contact_id)
            {
                //Check if the email matches with the existing customer contact id
                $contact        = CustomerContact::where('email', $request->email )->get();

                if(count($contact) > 0)
                {
                    $request['customer_contact_id'] = $contact->first()->id;                                        
                }
                else
                {
                    //Check if it's an existing potential customer otherwise add it
                    $contact = PotentialCustomer::where('email', $request->email)->get();

                    if(!(count($contact) > 0))
                    {
                       PotentialCustomer::create(['name' => $request->name, 'email' => $request->email]);
                       
                    }
                }    

            }
            


	        // Save the attachments (If exists)
            $files             = $request->attachment;
       
            if(is_array($files) && count($files) > 0)
            {
                $attachment = new Attachment();
                $attachment->add($files, $comment);       
            
            }

            // Send a notification email to customer/potential customer
            $email_cc   = Input::get('email_cc');            
            $mail       = Mail::to(Input::get('email'));                        
            if($email_cc)
            {
                $mail->cc($email_cc);
            }     

            $mail->send(new NewTicketCreated($ticket, $comment->body ));
         

            // Log Activity             
            $ticket->log_created();

            // Notify the team member who has been assigned the task to
            if($ticket->assigned_to && $ticket->assigned_to != auth()->user()->id)
            {
                $notifiable_member = User::find($ticket->assigned_to);

                if($notifiable_member)
                {
                    // Send the notification
                    Notification::send($notifiable_member, new TicketAssigned($ticket, auth()->user() ));

                    // Log Activity
                    $ticket->log_ticket_assigned($notifiable_member);  

                }
            }            
                           


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
            return redirect()->route('ticket_list');
        } 
        else 
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('ticket_list');
        }
    }


    function get_predefined_reply()
    {
    	$obj = PreDefinedReply::select('details')->find(Input::get('id'));

        if($obj)
        {
            return response()->json(['status' => 1, 'data' => $obj->toArray()]);
        }
        else
        {
            return response()->json(['status' => 2 ]);
        }
    }

    function show(Ticket $ticket)
    {
        $permission = check_perm(['tickets_view', 'tickets_view_own']);
        
        if(!$permission)
        {
            abort(404);
        }

        if(is_array($permission) && (!in_array('tickets_view', $permission ))  
            && in_array('tickets_view_own', $permission ) 
            && ($ticket->assigned_to != auth()->user()->id)
        )
        {
            abort(404);
        }
        
        $group_name     = app('request')->input('group');



    	$data = Ticket::dropdown();

        if($group_name == 'settings')
        {
            
            if($ticket->customer_contact_id)
            {
                 $data['customer_contact_id'] = [ $ticket->customer_contact_id => $ticket->name ] ;
            }

            if($ticket->project_id)
            {
                $project            = Project::with(['customer'])->find($ticket->project_id);

                if(!$project)
                {
                    abort(404);
                }         
           
                $data['project_id_list']   = [ $project->id =>  $project->name ] ;
            }
            
            $ticket->tag_id = $ticket->tags()->pluck('tag_id')->toArray();

        }



    	return view('support.show_ticket', compact('data'))->with('rec', $ticket);
    }

    

    

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [            
            'subject'               => 'required',
            'department_id'         => 'required',
            'name'                  => 'required',
            'email'                 => 'required|email',
            'ticket_priority_id'    => 'required'
   
            
            

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

            
            $previous_data_assigned_to  = $request['assigned_to'];

            // Saving Data        
            $ticket                     = Ticket::find($id);     
            $previously_assigned_to  = $ticket->assigned_to ;

            $ticket->update($request->all()) ;            

            // Tags
            $ticket->tag_sync($request->tag_id);

            //$ticket->log_updated();

            // If the ticket has been assigned to someone
            // If the previous assignee and the updated assignee are not the same
            // then let the previous user know that he has been uassigned from the ticket
            if($ticket->assigned_to && ($previously_assigned_to != $ticket->assigned_to) )
            {
                $notifiable_member = User::find($previously_assigned_to);

                if($notifiable_member)
                {
                    // Send the notification
                    Notification::send($notifiable_member, new TicketUnAssigned($ticket, auth()->user() )); 

                    // Log Activity
                    $ticket->log_ticket_unassigned($notifiable_member);   
                }
            }         

            // If the ticket has been assigned to someone
            // If the previous assignee and the updated assignee are not the same
            // if the updated assignee is not the current user
            // then send a notification            
            if($ticket->assigned_to && ($previously_assigned_to != $ticket->assigned_to) && $ticket->assigned_to != auth()->user()->id)
            {
                $notifiable_member = User::find($ticket->assigned_to);

                if($notifiable_member)
                {
                    // Send the notification
                    Notification::send($notifiable_member, new TicketAssigned($ticket, auth()->user() )); 

                    // Log Activity
                    $ticket->log_ticket_assigned($notifiable_member);   
                }
            }


            DB::commit();
            $success = true;
        } 
        catch (\Exception  $e) {           

            $success = false;
            DB::rollback();

        }

        if ($success)
        {

            session()->flash('message', __('form.success_update'));
            return redirect()->back();
        } 
        else 
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->back();
        }
    }

    function change_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                        => 'required',
            'ticket_status_id'          => 'required',             

        ]);

        if ($validator->fails()) 
        {
             return response()->json(['status' => 2 ,'errors'=>$validator->errors(), 'msg' => '' ]);
        }

        Ticket::where('id', Input::get('id'))->update(['ticket_status_id' => Input::get('ticket_status_id')]);  

        return response()->json(['status' => 1 , 'msg' => __('form.success_update') ]);
    }

    function add_reply(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                        => 'required',
            'details'                   => 'required',   
            'email_cc'                  => 'nullable|email',
            'ticket_status_id'          => 'required'
            

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

            // Update Ticket Status        
            $ticket                     = Ticket::find(Input::get('id')); 
            $ticket->ticket_status_id   = Input::get('ticket_status_id');
            $ticket->last_reply         = now();
            $ticket->save();

            // Save Comment
            $comment                    = new Comment();
            $comment->body              = Input::get('details');
            $comment->user_id           = Auth::user()->id;
            $comment->user_type         = USER_TYPE_TEAM_MEMBER;
            $ticket->comments()->save($comment);

            // Save the attachments (If exists)
            $files             = $request->attachment;
       
            if(is_array($files) && count($files) > 0)
            {
                $attachment = new Attachment();
                $attachment->add($files, $comment);       
            
            }

            // Log Activity
            $ticket->log_ticket_comment($comment);

            // Send Email to Customer or Potential Customers( who are not registered in the system as a customer)
            Mail::to($ticket->email, $ticket->name)->send(new TicketReply($ticket, $comment));          

            DB::commit();
            $success = true;
        } 
        catch (\Exception $e) {
            
            $success = false;
            DB::rollback();
           
        }

        if ($success)
        {
            
            session()->flash('message', __('form.success_submit'));

            if($request->return_to_ticket_list)
            {
                return redirect()->route('ticket_list');
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


    function add_note(Request $request, Ticket $ticket)
    {    

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
            $ticket->notes()->save($note);

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


    
    public function destroy(Ticket $ticket)
    {
        if($ticket->tasks->count() > 0)
        {
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
        else
        {
            DB::beginTransaction(); 

            try {
            
                $ticket->forcedelete();
                $ticket->delete_has_many_relations(['comments', 'notes']);
                
                DB::commit();

                session()->flash('message', __('form.success_delete'));
           
            } catch (\Illuminate\Database\QueryException $e) {
               // Handle Integrity constraint violation
                DB::rollback();
                session()->flash('message', __('form.delete_not_possible_fk'));
            }
            catch (\Exception  $e) {
                
                DB::rollback();
                session()->flash('message', __('form.could_not_perform_the_requested_action'));
                            
            }



        }
        
        return redirect()->route('ticket_list');
    }


    function configuration_page()
    {
        $records = Setting::where('option_key', 'support_configuration')->get();

        if(count($records) > 0)
        {
            $records = $records->first();
            $records = json_decode($records->option_value);

            $rec = new \stdClass();
            foreach ($records as $key=>$value)
            {
                $rec->{$key} = $value;
            }

        }
        else
        {
            $rec = new \stdClass();
        }

        return view('support.configuration')->with('rec', $rec);
    }

    function update_configuration_page(Request $request)
    {

        $obj = Setting::updateOrCreate(['option_key' => 'support_configuration' ]);
        $obj->option_value = json_encode([
            'disable_support'               => Input::get('disable_support'),
            'disable_knowledge_base'        => Input::get('disable_knowledge_base'),
            'knowledge_base_is_private'     => Input::get('knowledge_base_is_private'),
        ]);
        $obj->save();

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();
    }
}
