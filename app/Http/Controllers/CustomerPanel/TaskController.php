<?php

namespace App\Http\Controllers\CustomerPanel;

use App\Http\Controllers\Controller;
use App\Tax;
use App\Lead;
use App\Task;
use App\Comment;
use App\Project;
use App\Proposal;
use App\Customer;
use App\Attachment;
use App\TaskStatus;
use App\User;
use App\Notifications\MemberMentionedInTaskComment;
use App\Notifications\TaskAssigned;
use App\NumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CommentOnTask;

class TaskController extends Controller
{
    

    function kanban_view()
    {
        
        $task = new Task(); 
        $rec    = $task->get_data_for_kanban_view();
        return view('customer_panel.task.kanban_view', compact('data'))->with('rec', $rec ); 
    }

    function paginate()
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $q                  = Task::query();
        $query              = Task::orderBy('id', 'DESC')->with(['status', 'milestone', 'priority']);        
        $project_id         = Input::get('component_number');
        $status_id          = Input::get('status_id');
        
        $q->where('component_id', '=', COMPONENT_TYPE_PROJECT )
                ->where('component_number', '=', $project_id); 
            $query->where('component_id', '=', COMPONENT_TYPE_PROJECT )
                ->where('component_number', '=', $project_id);


        if($status_id)
        {
           $query->whereIn('status_id', (is_array($status_id)) ? $status_id : [$status_id] ); 
        }

        
        $number_of_records = $q->count();

        if($search_key)
        {
            $query->where('title', 'like', $search_key.'%')
                ->orWhere('start_date', '=', date2sql($search_key))
                ->orWhere('due_date', '=', date2sql($search_key))
                ->orWhereHas('status', function ($q) use ($search_key) {
                    $q->where('task_statuses.name', 'like', $search_key.'%');
                    
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

                $rec[] = array(

                    anchor_link($row->title, route('cp_show_task_page', [$project_id, $row->id])),
                    
                    ($row->start_date) ? sql2date($row->start_date) : "",
                    ($row->due_date) ? sql2date($row->due_date) : "",
                    $row->status->name,
                    (isset($row->milestone->name) && $row->milestone->name) ? $row->milestone->name : "",
                    ($row->is_billable) ? __('form.billable') : __('form.not_billable'),
                    $row->priority->name,
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
        $data = Task::dropdown();
        return view('task.create', compact('data'))->with('rec','');
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
            'title'         => 'required|max:190',
            'project_id'    => 'required'
        ]);

        if ($validator->fails())
        {
            if($request->ajax())
            {
                return response()->json(['status' => 2 ,'errors'=>$validator->errors()]);
            }
            else
            {
                return  redirect()->back()
                ->withErrors($validator)
                ->withInput();
            }

            
        }

        $request['number']              = NumberGenerator::gen(COMPONENT_TYPE_TASK);
        $request['start_date']          = date2sql($request->start_date) ;
        $request['due_date']            = date2sql($request->due_date)  ;
        $request['created_by']          = auth()->user()->id;
        $request['component_id']        = COMPONENT_TYPE_PROJECT;
        $request['component_number']    = $request->project_id;        
        $request['user_type']           = USER_TYPE_CUSTOMER;
        $request['status_id']           = TASK_STATUS_BACKLOG;
       

        $task  = Task::create($request->all());   
        

        // Save the attachments (If exists)
        $files                  = $request->attachment;
   
        if(!empty($files))
        {
            $attachment = new Attachment();
            $attachment->add($files, $task);       
        
        }

        // Add the user as a Follower 
        $task->add_follower(USER_TYPE_CUSTOMER, auth()->user()->id);

        if($request->ajax())
        {
            return response()->json(['status' => 1 ,'msg'=>  __('form.success_add') ]);
        }
        else
        {
            session()->flash('message', __('form.success_add'));
            return  redirect()->back();
        }

   


    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        $data = Task::dropdown();
        //$task->tag_id = $task->tags();
        $task->tag_id = $task->tags()->pluck('tag_id')->toArray();

        if($task->component_id)
        {
            $data['component_number_options'] = (isset($task->related_to->name)) ? [ $task->related_to->id => $task->related_to->name] : [];
        }
        if($task->milestone_id)
        {
            $data['milestone_options'] = (isset($task->milestone->name)) ? [ $task->milestone->id => $task->milestone->name] : [];
        }
        if($task->parent_task_id)
        {
            $data['parent_task_id_list'] = (isset($task->parent_task->title)) ? [ $task->parent_task->id => $task->parent_task->title] : [];
        }

        return view('task.create', compact('data'))->with('rec',$task);
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
          
            'title' => 'required|max:190'
        ];


        if(isset($request->component_id) && $request->component_id)
        {
            $rules['component_number'] = 'required';
        }
        $validator = Validator::make($request->all(), $rules, [
            'component_number.required' => __('form.this_field_is_required'),
        ]);

        if ($validator->fails())
        {
            return  redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $obj = Task::find($id);

        $request['start_date']      = date2sql($request->start_date) ;
        $request['due_date']        = date2sql($request->due_date)  ;

        $obj->update($request->all()) ; 

        $obj->tags()->sync($request->tag_id);

        // Add the user as a Follower 
        $task->add_follower(USER_TYPE_CUSTOMER, auth()->user()->id);

        session()->flash('message', __('form.success_update'));
        return  redirect()->route('task_list');


    }



    function post_task_comment(Request $request, Task $task)
    {
        $message = Input::get('comment');

        $comment            = new Comment();
        $comment->body      = $message;
        $comment->user_id   = Auth::user()->id;
        $comment->user_type = USER_TYPE_CUSTOMER;
        $task->comments()->save($comment);

        $files =  Input::get('attachment');

        if(!empty($files))
        {
            $attachment = new Attachment();
            $attachment->add($files, $task);      
        
        }

        // Add the user as a Follower 
        $task->add_follower(USER_TYPE_CUSTOMER, auth()->user()->id);

         // Notify Followers of New Comment
        $notifiable_members = $task->get_notifiable_members(['user_type' => USER_TYPE_CUSTOMER, 'user_id' => auth()->user()->id ]);        
        
        if($notifiable_members)
        {
            Notification::send($notifiable_members, new CommentOnTask($task , $comment, auth()->user() ));    
        }


        

        session()->flash('message', __('form.success_add'));
        return redirect()->back();
    }


    function update_task_comment(Request $request, Task $task, Comment $comment)
    {
        $message = Input::get('comment');
       
        $comment->body = $message;
        $comment->user_id = Auth::user()->id;
        $comment->user_type = USER_TYPE_TEAM_MEMBER;
        $comment->actual_updated_at = Carbon::now()->toDateTimeString();
        $task->comments()->save($comment);    

        // Notify Followers
        $notifiable_members = $task->get_notifiable_members(['user_type' => USER_TYPE_CUSTOMER, 'user_id' => auth()->user()->id ]);        
        
        if($notifiable_members)
        {
            Notification::send($notifiable_members, new CommentOnTask($task, $comment, auth()->user(), TRUE ));    
        }


        
        
        session()->flash('message', __('form.success_update'));
        return redirect()->back();
    }


    


    public function upload_attachment(Request $request)
    {
   
        $validator = Validator::make($request->all(), [
        
            'file'                    => 'required|max:1000',

        ]);

        if ($validator->fails()) 
        {
      
           return response()->json($validator->errors());
        }

        // Upload Attachment
        $attachment = Storage::putFile('public/tasks', $request->file('file') );
       
       $encrypt = encrypt([
            'name' => $attachment,
            'display_name' => $request->file->getClientOriginalName(),
        ]);



        return response()->json([
            'url' =>  asset(Storage::url($attachment)) ,
            'name' => $attachment,
            'display_name' => $request->file->getClientOriginalName(),
            'encrypted_value_for_input' => $encrypt
        ], 200);

    }


    function comments(Task $task)
    {

        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $comment_id         = Input::get('comment_id');  

        if($comment_id)
        {
            $q      = $task->comments()->where('comments.id', $comment_id);
            $query  = $task->comments()->where('comments.id', $comment_id);
        }
        else
        {
            $q      = $task->comments();
            $query  = $task->comments();
        }


        $number_of_records  = $q->get()->count();        
      

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();

        $rec = [];

        if (count($data) > 0)
        {
            foreach ($data as $key => $row)
            {

               if($row->actual_updated_at) 
               {
                    $moment = __('form.updated_at') . " " . Carbon::createFromTimeStamp(strtotime($row->actual_updated_at))->diffForHumans() ;
               }
               else
               {
                    $moment = Carbon::createFromTimeStamp(strtotime($row->created_at))->diffForHumans() ;
               }
               
               // On the user who posted the comment is allowed to edit the comment
              $action = "";

              if((Auth::user()->id == $row->user_id) && ($row->user_type == USER_TYPE_CUSTOMER))
              {
                     $action = '<div class="coment_area">
                            <a href="#" class="edit_comment"><i class="far fa-edit"></i></a>
                            <a href="'. route('delete_comment', $row->id).'" class="delete_item"><i class="far fa-trash-alt"></i></a>
                            </div>
                            
                            <div style="width:100% !important;" class="comment_form">
                        <form action="'. route('patch_task_comment', [$row->commentable_id, $row->id]).'"  method="POST">
                        '.method_field('PATCH'). csrf_field().' 
                        <div class="form-group">
                            <textarea name="comment" class="form-control form-control-sm comment">'.$row->body.'</textarea>
                           </div> 
                        <button type="submit" class="btn btn-sm btn-primary">'.__('form.submit').'</button>
                        <a href="#" class="btn btn-sm btn-light cancel_commenting">'.__('form.cancel').'</a>
                        </form>
                      <div>


                            ';
              }
              
              if(($row->user_type == USER_TYPE_TEAM_MEMBER))
              {
                $commenter = anchor_link($row->user->name, route('member_profile', $row->user_id));
              }
              else
              {
                $commenter = $row->user->name;
              }

                $rec[] = array( 
                    '<div class="media" href="#'.$row->id.'">
                            
                            <img class="mr-3 staff-profile-image-small mright5" src="'.get_avatar_small_thumbnail($row->user->photo).'" />
                            <div class="media-body coment_area">
                                <div class="mt-0">'.$commenter.'</div>
                                <small class="form-text text-muted">'.$moment.'</small>                 
                            
                            <p>'.$row->parsed_comment().'</p>

                            </div>
                            
                      '.$action.'                     
                      

                    </div>
                    '


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

