<?php

namespace App\Http\Controllers;

use App\Setting;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function general_information()
    {

        $data['dropdowns'] = Setting::settings_dropdown();

        $records = Setting::all();

        if(count($records) > 0)
        {
            $records = $records->toArray();

            $rec = new \stdClass();
            foreach ($records as $row)
            {
                $rec->{$row['option_key']} = $row['option_value'];
            }

        }       
        return view('setup.general', compact('data'))->with('rec', $rec);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_general_information(Request $request)
    {


        $rules = [
            'settings.company_name'                     => 'required',
            'settings.company_phone'                    => 'required',
            'settings.company_email'                    => 'required|email',
            'settings.company_address'                  => 'required',
            'settings.company_city'                     => 'required',
           'settings.company_country'                   => 'required',
            'settings.company_zip_code'                 => 'required',

            
            'settings.digit_grouping_symbol'            => 'required',
            'company_logo'                              => 'sometimes|required|max:1000|mimes:jpeg,bmp,png',
            'company_logo_internal'                     => 'sometimes|required|max:1000|mimes:jpeg,bmp,png',
            'favicon'                                   => 'sometimes|required|max:1000|mimes:ico',
        ];
        $msg = [

            'settings.company_name.required'            => sprintf(__('form.field_is_required'), __('form.company_name')),
            'settings.company_phone.required'           => sprintf(__('form.field_is_required'), __('form.phone')),
            'settings.company_email.required'           => sprintf(__('form.field_is_required'), __('form.email')),
            'settings.company_email.email'              => sprintf(__('form.valid_email'), __('form.email')),            
            'settings.company_address.required'         => sprintf(__('form.field_is_required'), __('form.address')),
            'settings.company_city.required'            => sprintf(__('form.field_is_required'), __('form.city')),
            'settings.company_country.required'         => sprintf(__('form.field_is_required'), __('form.country')),
            'settings.company_zip_code.required'        => sprintf(__('form.field_is_required'), __('form.zip_code')),            
            'settings.digit_grouping_symbol.required'   => sprintf(__('form.field_is_required'), __('form.thousand_seperator')),

        ];

        $validator = Validator::make($request->all(), $rules, $msg);


        if ($validator->fails())
        {          
          
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->settings)->with(['rec' =>  (object) $request->settings ]);
        }


        $settings = Input::get('settings');


        // Upload Attachment
        $attachment = NULL;
        if ($request->hasFile('company_logo'))
        {
            $folder_location    = 'public/uploads';
            
            $extension          = $request->file('company_logo')->extension();
            $file_name          = 'logo';
            $attachment         = Storage::putFileAs($folder_location, $request->file('company_logo'), $file_name.".".$extension);
            
            
            $file_location      = storage_path('app')."/".$attachment;

          

            $img = Image::make($file_location);

            // resize image to fixed size
            $img->resize(125, 34);          
           
            
            $company_logo_small = $folder_location."/".$file_name."_125x34.". $extension;

            $img->save(storage_path('app')."/". $company_logo_small);

        
            $settings['company_logo']       = $attachment;
            $settings['company_logo_small'] = $company_logo_small;

            
        }

        if ($request->hasFile('company_logo_internal'))
        {
            $folder_location    = 'public/uploads';
            
            $extension          = $request->file('company_logo_internal')->extension();
            $file_name          = 'logo_internal';
            $attachment         = Storage::putFileAs($folder_location, $request->file('company_logo_internal'), $file_name.".".$extension);
            
            
            $file_location      = storage_path('app')."/".$attachment;

          

            $img = Image::make($file_location);

            // resize image to fixed size
            $img->resize(125, 34);          
           
            
            $company_logo_small = $folder_location."/".$file_name."_125x34.". $extension;

            $img->save(storage_path('app')."/". $company_logo_small);

        
            $settings['company_logo_internal']       = $attachment;
            $settings['company_logo_internal_small'] = $company_logo_small;

            
        }

        if ($request->hasFile('favicon'))
        {
            $folder_location        = 'public/uploads';
            
            $extension              = $request->file('favicon')->extension();
            $file_name              = 'favicon';
            $settings['favicon']    = Storage::putFileAs($folder_location, $request->file('favicon'), $file_name.".".$extension);
            
        }

        
        $settings['enable_google_recaptcha'] = isset($settings['enable_google_recaptcha']) ? $settings['enable_google_recaptcha'] : NULL;

        $settings['disable_job_queue']       = isset($settings['disable_job_queue']) ? $settings['disable_job_queue'] : NULL;

        foreach ($settings as $key=>$value)
        {
            $obj = Setting::updateOrCreate(['option_key' => $key ]);
            $obj->option_value = $value;
            $obj->save();
        }

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();


    }


    function email()
    {

        $records = Setting::all();

        if(count($records) > 0)
        {
            $records = $records->toArray();

            $rec = new \stdClass();
            foreach ($records as $row)
            {
                $rec->{$row['option_key']} = $row['option_value'];
            }

        }
        else
        {
            $rec = new \stdClass();
        }

        $data['dropdowns']['email_sending_options'] = [
                        'smtp'      => 'SMTP', 
                        // 'sendmail'  => 'Sendmail',
                        'mailgun'   => 'Mailgun',
                        
                     ];
       return view('setup.email', compact('data'))->with('rec', $rec);
    }



    public function update_email(Request $request)
    {

        $settings = Input::get('settings');

        if($settings['company_email_send_using'] == 'mailgun')
        {
            $rules = [
                'settings.company_email_mailgun_domain'      => 'required',
                'settings.company_email_mailgun_key'         => 'required',
                'settings.company_email_from_address'        => 'required|email',
               
            ];
        }
        else
        {
            $rules = [
                'settings.company_email_smtp_host'      => 'required',
                'settings.company_email_smtp_port'      => 'required',
                'settings.company_email_from_address'   => 'required|email',
                'settings.company_email_smtp_password'  => 'required',     
            ];
        }


        


        $msg = [
    

            'settings.company_email_smtp_host.required' => sprintf(__('form.field_is_required'), __('form.smtp_host')),
            'settings.company_email_smtp_port.required' => sprintf(__('form.field_is_required'), __('form.smtp_port')),
            'settings.company_email_from_address.required' => sprintf(__('form.field_is_required'), __('form.email_from_address')),
            'settings.company_email_from_address.email' => sprintf(__('form.valid_email'), __('form.email_from_address')),
            
            'settings.company_email_smtp_password.required' => sprintf(__('form.field_is_required'), __('form.smtp_password')),
            'settings.company_email_mailgun_domain.required' => sprintf(__('form.field_is_required'), __('form.mailgun_domain')),
            'settings.company_email_mailgun_key.required' => sprintf(__('form.field_is_required'), __('form.mailgun_key')),

        ];

        $validator = Validator::make($request->all(), $rules, $msg);

      

        if ($validator->fails()) 
        {
            return redirect()->back()->withErrors($validator)                
                ->withInput($request->settings)->with(['rec' =>  (object) $request->settings ]);
        }


        if(!(isset($settings['disable_email'])))
        {
            $settings['disable_email'] = NULL;
        }

        
        foreach ($settings as $key=>$value)
        {
            $obj = Setting::updateOrCreate(['option_key' => $key ]);
            $obj->option_value = $value;
            $obj->save();
        }

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();


    }


    function send_test_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_email_address' => 'required|email'
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try{

             Mail::to($request->test_email_address)->send(new TestMail());
            session()->flash('message', __('form.email_sent'));
            return  redirect()->back();
        }
        catch(\Swift_TransportException $e){

            session()->flash('message', __('form.email_was_not_sent') );
            return  redirect()->back()->withInput();
        }
    }


    function pusher_page()
    {
        $records    = Setting::where('option_key', 'pusher')->get();
        $rec        = [];
        $data       = [];        

        if($records->count() > 0)
        {
            $rec = json_decode($records->first()->option_value);

        }

       return view('setup.pusher', compact('data'))->with('rec', $rec);
    }


    public function update_pusher(Request $request)
    {

        $validator = Validator::make($request->all(), [
                'app_id'      => 'required',
                'app_key'     => 'required',
                'app_secret'  => 'required',
          
            ]);

      

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }


        $obj = Setting::updateOrCreate(['option_key' => 'pusher' ]);
        $obj->option_value = json_encode([
            'app_id'                => Input::get('app_id'),
            'app_key'               => Input::get('app_key'),
            'app_secret'            => Input::get('app_secret'),
            'app_cluster'           => Input::get('app_cluster'),
            'is_enable'             => (Input::get('is_enable')) ? TRUE : FALSE,
        ]);
        $obj->save();

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();


    }

    function email_template_home()
    {
        $template_list = Setting::list_of_email_templates();

        return view('setup.email_template_main', compact('template_list'));
    }

    function email_template_page($template_name)
    {

        $data           = Setting::get_email_template_details_by_name($template_name);
        $template_list  = Setting::list_of_email_templates();

        if(!$data)
        {
            abort(404);
        }

        $rec = [];

        $records = Setting::where('option_key', $template_name)->get();

        if(count($records) > 0)
        {
            $records                    = json_decode($records->first()->option_value);
            $rec                        = new \stdClass();
            $rec->subject               = isset($records->subject) ? $records->subject : '' ;  
            $rec->template              = isset($records->template) ? $records->template : '' ; 
            $rec->from_name             = isset($records->from_name) ? $records->from_name : '' ;
            $rec->from_email_address    = isset($records->from_email_address) ? $records->from_email_address : ''  ;
        }       

       return view('setup.email_template', compact('data', 'template_list'))->with('rec', $rec);
    }
    

    public function update_email_template(Request $request)
    {

        $validator = Validator::make($request->all(), [
                'subject'       => 'required',        
                'template'      => 'required',
                'name'          => 'required', // Template Name
          
            ]);      

        if ($validator->fails()) 
        {
    
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $obj = Setting::updateOrCreate(['option_key' => $request->name ]);
        $obj->option_value = json_encode(['subject' => $request->subject, 'template' => $request->template, 'from_name' => $request->from_name, 'from_email_address' => $request->from_email_address ]);
        $obj->auto_load_disabled = TRUE;
        $obj->save();

        session()->flash('message', __('form.success_update'));
        return  redirect()->back();


    }


}
