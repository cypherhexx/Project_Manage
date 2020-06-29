<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;


use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return  \Illuminate\Http\Response
     */
    function index()
    {
        $data = [];
        return view('team_member.index', compact('data'));
    }


    function paginate()
    {
        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $q                  = User::query();
        $query              = User::orderBy('first_name', 'ASC')->with(['skills', 'role']);
                                

        /* If the current user doesn't have permission to view all members, then 
            show only the members who are also in him team 
        */
        if(!check_perm('team_members_view'))
        {   
            // Get the team member Ids 
            $sql = "SELECT user_id AS id FROM user_teams WHERE team_id IN (
                     SELECT team_id FROM user_teams WHERE user_id = ? 
                    ) GROUP BY user_id 
                     ";
            $team_member_ids = DB::select($sql, [ auth()->id() ]);

            if(count($team_member_ids) > 0)
            {
                $team_member_ids = array_map(function ($value) {
                    return (array)$value;
                }, $team_member_ids);
            }
            else
            {
                $team_member_ids = [];
            }
           
            $q->whereIn('id', $team_member_ids);
            $query->whereIn('id', $team_member_ids);
        }                                


        $number_of_records  = $q->count();
        


        if($search_key)
        {
            $query->where('first_name', 'like', $search_key.'%')
                ->orWhere('last_name', 'like', $search_key.'%')
                ->orWhere('code', 'like', $search_key.'%')
                ->orWhere('job_title', 'like', $search_key.'%')
                ->orWhere('phone', 'like', $search_key.'%')
                ->orWhere('email', 'like', $search_key.'%')
                // ->orWhereHas('teams', function ($q) use ($search_key) {
                //     $q->where('teams.name', 'like', $search_key.'%');
                // })
                ->orWhereHas('skills', function ($q) use ($search_key) {
                    $q->where('tags.name', 'like', $search_key.'%');
                    
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

                if(check_perm('team_members_edit'))
                {
                    $checked     = ($row->inactive) ? '' : 'checked';
                    $active_status = ' <input '.$checked.' data-id="'.$row->id.'" class="tgl tgl-ios customer_status" id="cb'.$row->id.'" type="checkbox"/><label class="tgl-btn" for="cb'.$row->id.'"></label>';
                }
                else
                {
                    $active_status = ($row->inactive) ? __('form.no') : __('form.yes');
                }
                $actions = [];

                $actions[0]  = [
                            'action_link' => route('edit_team_member_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission'  => 'team_members_edit',
                        ];

                // If the team member is the currently logged in user, do not allow to delete 

                if($row->id != auth()->user()->id)
                {
                    $actions[1]  = [
                            'action_link' => $row->id , 
                            'action_text' => __('form.delete'), 'action_class' => 'delete_team_member',
                            'permission'  => 'team_members_delete',
                        ];
                }        

                $rec[] = array(
                    a_links(anchor_link($row->first_name . " ". $row->last_name , route('member_profile', $row->id)), $actions),
                    $row->code,
                    $row->job_title,
                    $row->email,
                    $row->phone,
                    // $active_status,
               
                    (isset($row->role)) ? $row->role->name : ''

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

        $data   = User::dropdowns();
        return view('team_member.main', compact('data'))->with('rec', []);
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
            'code'          => 'nullable|unique:users',
            'first_name'    => 'required',
            'last_name'     => 'required',
            'email'         => 'required|email|unique:users',
            'job_title'     => 'required',
            'password'      => 'nullable|min:6',
            'salary'        => 'nullable|numeric'

        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        

        $obj = new User();     

        $obj->short_code        = gen_team_member_short_code($request->first_name . " ". $request->last_name);
        $obj->code              = $request->code;
        $obj->first_name        = $request->first_name;
        $obj->last_name         = $request->last_name;
        $obj->email             = $request->email;
        $obj->password          = Hash::make($request->password ?? DEFAULT_USER_PASSWORD) ;
        $obj->gender_id         = $request->gender_id;
        $obj->birth_date        = date2sql($request->birth_date);
        $obj->phone             = $request->phone;
        $obj->address           = $request->address;
        $obj->job_title         = $request->job_title;
        $obj->salary            = $request->salary;
        $obj->salary_term       = $request->salary_term;
        $obj->joining_date      = date2sql($request->joining_date);
        $obj->reporting_boss    = $request->reporting_boss;
        $obj->facebook          = $request->facebook;
        $obj->linked_in         = $request->linked_in;
        $obj->skype             = $request->skype;
        $obj->email_signature   = $request->email_signature;
        $obj->is_administrator  = $request->is_administrator;
        $obj->role_id           = $request->role_id;


        $obj->save();

        $obj->teams()->attach($request->team_id);

        $obj->skills()->attach($request->skill_id);

        $obj->departments()->attach($request->department_id);

        session()->flash('message', __('form.success_add'));
        return redirect()->route('team_members_list');
    }

    /**
     * Display the specified resource.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function profile($user_id)
    {

        $user   = User::find($user_id);
        $group  = request()->query('group');
        $data   = [];
        if($group=='notifications' && !(is_current_user($user_id)))
        {
            abort(404);
        }
    
        return view('team_member.main', compact('data'))->with('rec', $user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function edit(User $member)
    {
    //    if(request()->headers->get('referer') == route('edit_team_member_page', $member->id))
    //    {
    //         $member = new stdClass;
    //    }
        $data   = User::dropdowns();
        $member->team_id        = $member->teams()->pluck('team_id')->toArray();
        $member->skill_id       = $member->skills()->pluck('skill_id')->toArray();
        $member->department_id  = $member->departments()->pluck('department_id')->toArray();
        return view('team_member.main', compact('data'))->with('rec',$member);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param    \Illuminate\Http\Request $request
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function update(Request $request, User $member)
    {
        
        $validator = Validator::make($request->all(), [
            'code'         => [ 'nullable',                               
                                Rule::unique('users')->ignore($member->id),
            ],
            'first_name'    => 'required',
            'last_name'     => 'required',         
            'email'         => [
                                'required',
                                Rule::unique('users')->ignore($member->id),
            ],
            'job_title'     => 'required',
            'password'      => 'sometimes|nullable|min:6',

        ]);

        if ($validator->fails()) {


            return redirect()->back()
                ->withErrors($validator)
                ->withInput(Input::all());
        }

       
        $obj = $member;
        $obj->code              = $request->code;
        $obj->first_name        = $request->first_name;
        $obj->last_name         = $request->last_name;
        $obj->email             = $request->email;

        if($request->password)
        {
            $obj->password          = Hash::make($request->password) ;
        }

        
        $obj->gender_id         = $request->gender_id;
        $obj->birth_date        = date2sql($request->birth_date);
        $obj->phone             = $request->phone;
        $obj->address           = $request->address;
        $obj->job_title         = $request->job_title;
        $obj->salary            = $request->salary;
        $obj->salary_term       = $request->salary_term;
        $obj->joining_date      = date2sql($request->joining_date);
        $obj->reporting_boss    = $request->reporting_boss;
        $obj->facebook          = $request->facebook;
        $obj->linked_in         = $request->linked_in;
        $obj->skype             = $request->skype;
        $obj->email_signature   = $request->email_signature;
        $obj->is_administrator  = $request->is_administrator;
        $obj->role_id           = $request->role_id;


        $obj->save();

        
        $obj->teams()->sync($request->team_id);

        $obj->skills()->sync($request->skill_id);

        $obj->departments()->sync($request->department_id);

        session()->flash('message', __('form.success_update'));
        return redirect()->route('team_members_list');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param    int $id
     * @return  \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {

       $validator = Validator::make($request->all(), [        
            'user_to_delete'    => 'required',
            'assigned_to'       => 'required',        

        ], [
            'user_to_delete.required' => __('form.user_to_delete.required'),
            'assigned_to.required' => '',
        ]);

        if ($validator->fails()) 
        {

            session()->flash('validation_message', implode("<br>", $validator->errors()->all()) );
            return redirect()->back()
                ->withErrors($validator)
                ->withInput(Input::all());
        }        


        
        DB::beginTransaction(); 

        try {   
                             
                $member = User::find(Input::get('user_to_delete'));
                $member->delete();  
               
                $member->action_upon_deleting_a_team_member(Input::get('assigned_to'));
     

                // Log Activity
                $description    = sprintf(__('form.act_deleted'), __('form.team_member')); 
                session()->flash('message', __('form.success_delete'), $member->first_name . " " . $member->last_name );

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

        

        return redirect()->back();

    }


    function get_members_for_suggestion_list()
    {
        $q = Input::get('q');

        if($q)
        {
            $users = User::select(DB::raw("CONCAT(first_name,' ',last_name) AS name"),'short_code AS key')
            ->where('first_name','like', $q .'%')
            ->orWhere('first_name','like', $q .'%')
            ->get();

            if(count($users) > 0)
            {
                return response()->json($users->toArray());
            }
            else
            {
                return response()->json([]);
            }
        }
    }

    public function update_account(Request $request)
    {

        $password = auth()->user()->password;

        $validator = Validator::make($request->all(), [
           
            'current_password' => [
                'required',
                
                function($attribute, $value, $fail) use ($password) {
                    
             

                    if (!Hash::check($value , $password )) {
                        return $fail(__('form.current_password_is_not_valid'));
                    }
                },
            ],
            
            'password'              => 'required|confirmed',

        ]);

        if ($validator->fails()) {
            return  redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $obj  = User::find(auth()->id());      
     

        if(isset($request->password) && $request->password != '')
        {
            $obj->password      = Hash::make($request->password) ;
        }


        $obj->save();

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();


    }
    

    function change_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [        
            'file'                    => 'required|max:1000|mimes:jpeg,bmp,png',
            'member_id'               => 'required',  

        ]);

        if ($validator->fails()) 
        {
      
           return response()->json(['status' => 2, 'msg' => implode(", ", $validator->errors()->all() ) ]);
        }

        // Upload Attachment
        $attachment = NULL;

        if ($request->hasFile('file'))
        {
            try{

                $folder_location    = 'public/uploads/avatars';
            
                $extension          = $request->file('file')->extension();
                $file_name          = uniqid().Input::get('member_id');
                $attachment         = Storage::putFileAs($folder_location, $request->file('file'), $file_name.".".$extension);
                
                
                $file_location      = storage_path('app')."/".$attachment;

              

                $img = Image::make($file_location);


               
                // resize the image to a width of 300 and constrain aspect ratio (auto height)
                $img->resize(32, 32, function ($constraint) {
                    $constraint->aspectRatio();
                });
               
                $img->save(storage_path('app')."/". $folder_location."/".$file_name."_32x32.". $extension);

               
                $member = User::find(Input::get('member_id'));

                // Delete previous avatar
                if($member->photo)
                {
                    Storage::delete($member->photo);

                    $old_avatar = pathinfo($member->photo, PATHINFO_FILENAME);
                    $old_avatar_small = str_replace($old_avatar, $old_avatar.AVATAR_SMALL_THUMBNAIL_SIZE, $member->photo );
                    Storage::delete($old_avatar_small);
                }

                $member->photo = $attachment;
                $member->save();

                return response()->json(['status' => 1, 'file_url' => asset(Storage::url($attachment)) ]);
            }
            catch(\Exception $e)
            {
                return response()->json(['status' => 2, 'msg' => "" ]);
            }
        }
        
    }

    function get_unread_notifications()
    {
        $notifications = auth()->user()->unreadNotifications()->orderBy('created_at', 'DESC')->get();
        $records = [];
        if(count($notifications) > 0)
        {
            foreach ($notifications as $notification) 
            {
                
                $data               = $notification->data;
                $data['moment']     = $notification->created_at->diffForHumans();
                $data['url']        = route('notification_redirect_url', $notification->id ) ;
                $records[]          = $data;
            }
        }

        return response()->json($records);
    }

    function notifications(User $member)
    {
        if(!(is_current_user($member->id)))
        {
            abort(404);
        }
       
        return view('team_member.notifications', compact('data'));
    }

    function notification_redirect_url($id)
    {
        if($id)
        {
            $notification   = auth()->user()->notifications->where('id', $id);

            if(count($notification) > 0)
            {
                $notification   = $notification->first();
                $url            = $notification->data['url'];         
                $notification->markAsRead();

                return redirect()->to($url);
            }
            
        }
        abort(404);
    }

    function mark_all_notification_as_read()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->back();
    }


    function notification_paginate()
    {
        $query_key          = Input::get('search');
        $search_key         = $query_key['value'];
        $number_of_records  = auth()->user()->notifications()->count();
        $query              = auth()->user()->notifications();      

        $recordsFiltered = $query->get()->count();
        $query->skip(Input::get('start'))->take(Input::get('length'));
        $data = $query->get();

        $rec = [];

        if (count($data) > 0)
        {
            foreach ($data as $notification)
            {

               $rec[] = [      
                   anchor_link($notification->data['message'], route('notification_redirect_url', $notification->id ) ),
                   $notification->created_at->diffForHumans(),
                   ($notification->read_at) ? __('form.read') : __('form.unread')

               ];                

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


    function search_team_member()
    {
        $search_key = Input::get('search');


        $data = User::select(DB::raw("CONCAT(first_name,' ',last_name) AS name, id"))
            ->where('id', '<>', Input::get('user_to_delete'))
            ->where(function ($query) use($search_key){
                $query->where('first_name', 'like', $search_key.'%')->orWhere('last_name', 'like', $search_key.'%');                
            })->get();            


        $results = ($data->count() > 0) ? $data : [];

        return response()->json([
            'results' => $results
        ]);
    }

}