<?php

namespace App\Http\Controllers;



use App\Rules\ValidDate;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;


use App\Attachment;

class AttachmentController extends Controller
{
   
    function download($filename)
    {

        try {
            $file = Crypt::decryptString($filename);

            return Storage::download($file);

        } catch (DecryptException $e) {
            //
            abort(404);
        }
    }


    public function upload(Request $request)
    {
   
        $validator = Validator::make($request->all(), [
        
            'file'                    => 'required|max:1000',

        ]);

        if ($validator->fails()) 
        {
      
           return response()->json($validator->errors());
        }

        // Upload Attachment
        $attachment = Storage::putFile('public/attachments', $request->file('file') );
        $short_code = create_short_code_for_attachment();
      



        return response()->json([
            'url'                       =>  asset(Storage::url($attachment)) ,
            'name'                      => $attachment,
            'display_name'              => $request->file->getClientOriginalName(),
            'encrypted_value_for_input' => encrypt([
                                                    'name' => $attachment,
                                                    'display_name' => $request->file->getClientOriginalName(),
                                                    'short_code' =>$short_code,
                                                ]),
            'short_code'                => $short_code,
        ], 200);

    }


    function delete_temporary_attachment()
    {
        $filename = Input::get('filename');
        
        if($filename)
        {
            Storage::delete($filename);
        }
        
    }

    public function destroy(Attachment $attachment)
    {
        Storage::delete($attachment->name);

        $attachment->delete();

        session()->flash('message', __('form.success_delete'));
        return  redirect()->back();
    }


    function change_profile_photo(Request $request)
    {
        $validator = Validator::make($request->all(), [        
            'file'                      => 'required|max:1000|mimes:jpeg,bmp,png',
            'profile_id'                => 'required',  
            'component_id'              => 'required',  

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

                if($request->component_id == COMPONENT_TYPE_CUSTOMER)
                {
                    $profile = \App\Customer::find(Input::get('profile_id')); 
                                      
                }
                else if($request->component_id == COMPONENT_TYPE_LEAD)
                {
                    $profile = \App\Lead::find(Input::get('profile_id'));  
                }
                else
                {   
                    // Team Member
                    $profile = \App\User::find(Input::get('profile_id'));                         
                }
                              

                // Delete previous avatar from disk
                if($profile->photo)
                {
                    Storage::delete($profile->photo);

                    $old_avatar         = pathinfo($profile->photo, PATHINFO_FILENAME);
                    $old_avatar_small   = str_replace($old_avatar, $old_avatar.AVATAR_SMALL_THUMBNAIL_SIZE, $profile->photo );
                    Storage::delete($old_avatar_small);
                }

                // Save New Avatar
                $profile->photo = $attachment;
                $profile->save();

                return response()->json(['status' => 1, 'file_url' => asset(Storage::url($attachment)) ]);
            }
            catch(\Exception $e)
            {
                return response()->json(['status' => 2, 'msg' => "" ]);
            }
        }
        
    }
}
