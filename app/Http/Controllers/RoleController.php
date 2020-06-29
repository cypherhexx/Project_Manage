<?php

namespace App\Http\Controllers;

use App\Permission;
use App\Role;
use App\User;
use App\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class RoleController extends Controller
{
    function index()
    {
        
        return view('role.index');
    }

    function paginate()
    {

        $query_key = Input::get('search');
        $search_key        = $query_key['value'];
        $number_of_records = Role::all()->count();


        $query = Role::orderBy('id', 'DESC');


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

                $rec[] = array(

                     a_links(anchor_link($row->name, route('edit_role_page', $row->id)), [
                        [
                            'action_link' => route('edit_role_page', $row->id), 
                            'action_text' => __('form.edit'), 'action_class' => '',
                            'permission'  => 'settings_',
                        ],
                         [
                            'action_link' => route('delete_role', $row->id), 
                            'action_text' => __('form.delete'), 'action_class' => 'delete_item',
                            'permission'  => 'settings_',
                        ]
                     ]),

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

    public function create()
    {
        $data = Role::dropdowns();
        return view('role.create', compact('data'))->with('rec', "");
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles',
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

            $obj                = new Role();
            $obj->name          = $request->name ;
            $obj->save();

            if(isset($request->permissions) && !empty($request->permissions))
            {
                foreach ($request->permissions as $key => $value) 
                {
                    $permission = new RolePermission();

                    $permission->role_id    = $obj->id;
                    $permission->name       = $key;
                    $permission->value      = $value;

                    $permission->save();
                }
            }

            DB::commit();
            $success = true;

        }
        catch (\Exception  $e)
        {
            $success = false;
            DB::rollback();
        }

        if ($success)
        {
            // the transaction worked ...
            session()->flash('message', __('form.success_add'));
            return redirect()->route('role_list');
        }
        else
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('role_list');
        }

    }

    


    public function edit(Role $role)
    {
        $perm = $role->permissions()->get();
        $p = [];
        if(count($perm) > 0)
        {
            $perm = $perm->toArray();
            foreach ($perm as $key => $row) 
            {
                $p[$row['name']] = $row;
            }    
        }
        $role->perm = $p;        

        $data = Role::dropdowns($role->perm);
        
        return view('role.create', compact('data'))->with('rec', $role);
    }


    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
           
            'name' => [
                'required',
                Rule::unique('roles')->ignore($request->id),
            ],

        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();

        }

        DB::beginTransaction();
        $success = false;

        try {

            $obj            = Role::find($id);
            $obj->name      = $request->name ;
            $obj->save();

           
            $existing_perms     = [];
            $perms              = $obj->permissions()->get();

            if(count($perms) > 0)
            {
                $existing_perms = array_column($perms->toArray() , 'name');
            }

           if(isset($request->permissions) && !empty($request->permissions))
            {
                $diff = array_diff($existing_perms , array_keys($request->permissions));

                if($diff && count($diff) > 0)
                {
                    $obj->permissions()->whereIn('name', $diff)->delete();  
                }                

                foreach ($request->permissions as $key => $value) 
                {
                    $permission = RolePermission::updateOrCreate(['name' => $key, 'role_id' => $obj->id ]);                  
                    $permission->value      = $value;

                    $permission->save();
                }
            }
            else
            {
                // Delete all permission
                $obj->permissions()->delete();  
            }

            DB::commit();
            $success = true;

        }
        catch (\Exception $e)
        {

            $success = false;
            DB::rollback();
        }

        if ($success)
        {
            // the transaction worked ...
            session()->flash('message', __('form.success_update'));
            // return redirect()->route('role_list');
            return redirect()->back();
        }
        else
        {
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
            return redirect()->route('role_list');
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    

    public function destroy(Role $role)
    {
        DB::beginTransaction(); 

        try {                 

             $role->permissions()->delete();    
             $role->delete();
             
             

            // Log Activity
            $description    = sprintf(__('form.act_deleted'), __('form.user_role'));       
            log_activity($role, trim($description), $role->name);  

            session()->flash('message', __('form.success_delete'));

            DB::commit();

        } catch (\Illuminate\Database\QueryException $e) {
           // Handle Integrity constraint violation
            DB::rollback();
            session()->flash('message', __('form.delete_not_possible_fk'));
        }
        catch (\Exception  $e) {
            
            DB::rollback();
            session()->flash('message', __('form.could_not_perform_the_requested_action'));
             
        }

        return redirect()->route('role_list');

    }
}
