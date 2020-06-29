<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::prefix('install')->group(function (){

    Route::get('/', 'SystemController@index')->name('installer_page');
    Route::get('/system-check', 'SystemController@index')->name('installer_page');
    Route::get('/general', 'SystemController@general_information')->name('run_installation_step_2_page');
    Route::post('/general', 'SystemController@store_general_information')->name('run_installation_step_2');

    Route::get('/database', 'SystemController@database_information')->name('run_installation_step_3_page');
    Route::post('/database', 'SystemController@setup_database_connection')->name('run_installation_step_3');
    Route::post('/run', 'SystemController@run_page')->name('run_installation_step_4_page');
    Route::post('/setup/db', 'SystemController@setup_database')->name('run_installation_step_4');
    Route::get('/status', 'SystemController@installation_result')->name('installation_result');
    Route::get('/failed', 'SystemController@installation_failed')->name('installation_failed');
     Route::get('/download/{path}', 'SystemController@download')->name('download_error_log');

});


/*
        Files We are worked in to enable customer guard
        modified:   app/CustomerContact.php
        modified:   app/Exceptions/Handler.php
        modified:   app/Http/Controllers/Auth/LoginController.php
        modified:   app/Http/Middleware/RedirectIfAuthenticated.php
        modified:   config/auth.php
        modified:   routes/web.php

        app/Http/Controllers/Auth/CustomerLoginController.php
        app/Http/Controllers/CustomerPanel/
        resources/views/auth/customer_login.blade.php
        resources/views/customer_panel/



     */






// Set Global Configuration/constants values from database using Middleware
Route::group(['middleware' => ['set_global_config']], function () {

Auth::routes();



// -------------------------------- Publicly Accesable Routes (Anyone with the URL can view) ------------------------------------------

Route::post('/proposal/accept/{proposal}', 'ProposalController@accept_proposal')->name('accept_proposal');
Route::post('/proposal/decline/{proposal}', 'ProposalController@decline_proposal')->name('decline_proposal');
Route::get('/proposals/view/{proposal?}/{url_slug}', 'ProposalController@customer_view')->name('proposal_customer_view');
Route::get('/proposals/download/{proposal?}', 'ProposalController@download_proposal')->name('download_proposal');
Route::get('/attachment/download/{encrypted_url}', 'AttachmentController@download')->name('attachment_download_link');
Route::get('/invoice/view/{invoice?}/{url_slug}', 'InvoiceController@customer_view')->name('invoice_customer_view');
Route::get('/invoices/download/{invoice?}', 'InvoiceController@download_invoice')->name('download_invoice');
Route::get('/invoices/make/payment/', 'InvoiceController@process_payment_request')->name('process_payment_request');

Route::get('/process/payment', 'InvoiceController@process_payment_request')->name('process_payment_request');

Route::get('/estimate/view/{estimate}/{url_slug}', 'EstimateController@customer_view')->name('estimate_customer_view');
Route::get('/estimates/download/{estimate?}', 'EstimateController@download_estimate')->name('download_estimate');
Route::post('/estimate/accept/{estimate}', 'EstimateController@accept_estimate')->name('accept_estimate');
Route::post('/estimate/decline/{estimate}', 'EstimateController@decline_estimate')->name('decline_estimate');
Route::get('/credit-note/download/{credit_note}', 'CreditNoteController@download_credit_note')->name('download_credit_note');

Route::post('/invoices/payment/process/stripe', 'InvoiceController@process_stripe_payment')->name('process_stripe_payment');

// -------------------------------- Publicly Accessible Routes ------------------------------------------










// -------------------------------- Clients Login & Registration Area (Publicly Accessible) ------------------------------------------

Route::prefix('client')->group(function (){

    Route::get('/login', 'Auth\CustomerLoginController@show_login_form')->name('customer_login_page');
    Route::post('/login', 'Auth\CustomerLoginController@login')->name('customer_login_submit');
    Route::get('/logout', 'Auth\CustomerLoginController@logout')->name('customer_logout');


    // Password Reset
    Route::post('/password/email', 'Auth\ClientForgotPasswordController@sendResetLinkEmail')->name('customer_password_email');
    Route::get('/password/reset', 'Auth\ClientForgotPasswordController@showLoginRequestForm')->name('customer_password_request');
    Route::post('/password/reset', 'Auth\ClientResetPasswordController@reset');
    Route::get('/password/reset/{token}', 'Auth\ClientResetPasswordController@showResetForm')->name('customer_password_reset');

    
    // Customer Registration 
    Route::group(['prefix' => 'register', 'middleware' => ['feature_enabled:customer_registration'] ] , function (){  

        Route::get('/', 'Auth\RegisterController@showRegistrationForm')->name('customer_registration_page');
        Route::post('/', 'Auth\RegisterController@register')->name('register');
        Route::get('/resend/vlink', 'Auth\RegisterController@resend_verification_link_page')->name('verification_resend');
        Route::post('/resend/vlink', 'Auth\RegisterController@resend_verification_link')->name('post_verification_resend');
        Route::get('/verify/{code}', 'Auth\RegisterController@verify_email')->name('verify_email');

    });

    // -------------------------------- knowledge-base ------------------------------------------


Route::group(['middleware' => 'public_access:customer' ], function () {
    // App\Http\Kernel.php
    // App\Http\Middleware\PublicAccess
    Route::prefix('knowledge-base')->group(function (){

        Route::get('/', 'CustomerPanel\KnowledgeBaseController@index')->name('knowledge_base_home');
        Route::get('{slug}', 'CustomerPanel\KnowledgeBaseController@article')->name('knowledge_base_article_customer_view');
        Route::get('category/{slug}', 'CustomerPanel\KnowledgeBaseController@category')->name('knowledge_base_category_customer_view');
        Route::get('search/articles', 'CustomerPanel\KnowledgeBaseController@search')->name('knowledge_base_search_customer_view');

    });

 });
    // -------------------------------- End of knowledge-base ------------------------------------------


});
// -------------------------------- End of Clients Login & Registration Area (Publicly Accessible) -------------------------------------


// -------------------------------- Clients Panel (Only Accessible by Clients) ------------------------------------------

Route::group(['middleware' => ['auth:customer' , 'load_settings_for_customer_panel'] ], function () {


 Route::prefix('client')->group(function (){




    Route::get('/', 'CustomerPanel\DashboardController@index')->name('customer_dashboard');
    // Route::get('/', 'CustomerPanel\ProjectsController@index')->name('customer_dashboard');

    // Self Account
    Route::get('/users/profile', 'CustomerPanel\DashboardController@user_profile')->name('cp_user_profile');
    Route::patch('/users/profile', 'CustomerPanel\DashboardController@update_profile')->name('cp_patch_user_profile');

    Route::get('/users/profile/change/password', 'CustomerPanel\DashboardController@change_password')->name('cp_change_password');

    Route::patch('/users/profile/change/password', 'CustomerPanel\DashboardController@update_password')->name('cp_patch_change_password');



    Route::get('/estimates', 'CustomerPanel\DashboardController@estimates')->name('cp_estimate_list');
    Route::get('/invoices', 'CustomerPanel\DashboardController@invoices')->name('cp_invoice_list');


    Route::post('/invoices', 'CustomerPanel\ProjectsController@paginate_invoices')->name('cp_datatable_invoices');
    Route::post('/estimates', 'CustomerPanel\ProjectsController@paginate_estimates')->name('cp_datatable_estimates');


    Route::get('/projects', 'CustomerPanel\ProjectsController@index')->name('customer_panel_projects_list');
    Route::post('/projects', 'CustomerPanel\ProjectsController@paginate')->name('customer_panel_datatable_projects_list');
    Route::get('/projects/view/{project}', 'CustomerPanel\ProjectsController@show')->name('cp_show_project_page');
    Route::post('/projects/milestones/{project}', 'CustomerPanel\ProjectsController@paginate_milestone')->name('cp_datatable_project_milestones');

    
 Route::post('/projects/attachment/create/{project}', 'CustomerPanel\ProjectsController@add_attachment')->name('cp_project_add_attachment');
        Route::post('/projects/attachments/get/{project}', 'CustomerPanel\ProjectsController@get_attachments')->name('cp_project_attachment_datatable');

    Route::post('/attachment/upload', 'AttachmentController@upload')->name('cp_upload_attachment');
    Route::post('/attachment/temporary/remove', 'AttachmentController@delete_temporary_attachment')->name('cp_delete_temporary_attachment');

    Route::post('/timesheets/{project}', 'CustomerPanel\ProjectsController@paginate_timesheet')->name('cp_datatables_timesheet');

    Route::get('/projects/view/{project}?group=tasks&subgroup=details&task_={task}', 'CustomerPanel\ProjectsController@index')->name('cp_show_task_page');

    Route::get('/projects/view/{project}?group=tasks&subgroup=details&task_={task}&comment={comment_id}', 'CustomerPanel\ProjectsController@index')->name('cp_show_task_comment');

   

   Route::group(['prefix' => 'tickets', 'middleware' => ['feature_enabled:support'] ] , function (){    
 
        
    Route::get('/', 'CustomerPanel\TicketController@index')->name('cp_ticket_list');
    Route::post('/list', 'CustomerPanel\TicketController@paginate')->name('cp_datatables_tickets');
    Route::get('/create', 'CustomerPanel\TicketController@create')->name('cp_add_ticket_page');
    Route::post('/create', 'CustomerPanel\TicketController@store')->name('cp_post_ticket');    
    Route::get('/view/{ticket}', 'CustomerPanel\TicketController@show')->name('cp_show_ticket_page');
    Route::post('/reply/{ticket}', 'CustomerPanel\TicketController@add_reply')->name('cp_ticket_add_reply');
        
        
    });



    Route::prefix('tasks')->group(function (){

        Route::post('/list', 'CustomerPanel\TaskController@paginate')->name('cp_datatables_tasks');
        Route::post('/create', 'CustomerPanel\TaskController@store')->name('cp_post_task');
      
        Route::post('/comments/list/{task}', 'CustomerPanel\TaskController@comments')->name('cp_datatable_tasks_comments');
        Route::post('/comments/save/{task}', 'CustomerPanel\TaskController@post_task_comment')->name('cp_post_task_comment');
        //Route::get('/show/{task}?comment={comment_id}', 'TaskController@show')->name('show_task_comment');
        
    });


      Route::get('/statement', 'CustomerPanel\DashboardController@customer_statement_page')->name('cp_customer_statement_page');


    
});

});



// -------------------------------- Clients Panel (Only Accessible by Clients) ------------------------------------------




    // -------------------------------- Application Users --------------------------------

Route::group(['middleware' => ['auth', 'set_user_permission'] ], function () {

    Route::get('/laravel-filemanager', '\UniSharp\LaravelFilemanager\Controllers\LfmController@show')->name('get_laravel_file_manager');
    Route::post('/laravel-filemanager/upload', '\UniSharp\LaravelFilemanager\Controllers\UploadController@upload');





    
    Route::post('/attachment/upload', 'AttachmentController@upload')->name('upload_attachment');
    Route::post('/attachment/temporary/remove', 'AttachmentController@delete_temporary_attachment')->name('delete_temporary_attachment');
    
     Route::get('/attachment/delete/{attachment}', 'AttachmentController@destroy')->name('remove_attachment');

    Route::post('/attachment/profile/photo', 'AttachmentController@change_profile_photo')->name('change_profile_photo');






Route::get('/filemanager', 'HomeController@file_manager')->name('file_manager');


    Route::get('/', 'HomeController@index')->name('dashboard');

    Route::post('/stat', 'HomeController@stats')->name('dashboard_stat');

    Route::get('/global/search', 'HomeController@global_search')->name('global_search');

// Self Account

    Route::patch('/users/account/update', 'UserController@update_account')->name('update_user_account');



// Route::get('/customer-group', 'CustomerGroupController@index')->name('customer_groups_list');
// Route::get('/customer-group/create', 'CustomerGroupController@create')->name('add_customer_groups');
// Route::get('/country', 'CountryController@index')->name('countries_list');
// Route::get('/country/create', 'CountryController@create')->name('add_countries');



Route::post('/notifications/unread', 'UserController@get_unread_notifications')->name('get_unread_notifications');
// Customer

    Route::prefix('customers')->group(function (){

        Route::get('/', 'CustomerController@index')->name('customers_list');

        Route::post('/paginated/list', 'CustomerController@paginate')->name('datatables_customers')->middleware('perm:customers_view');

        Route::get('/create/{lead?}', 'CustomerController@create')->name('add_customer_page')->middleware('perm:customers_create');
        Route::post('/save', 'CustomerController@store')->name('post_customer')->middleware('perm:customers_create');
        Route::get('/edit/{customer}', 'CustomerController@edit')->name('edit_customer_page')->middleware('perm:customers_edit');

        Route::get('/profile/{customer}', 'CustomerController@profile')->name('view_customer_page')->middleware('perm:customers_view');

        Route::patch('/save/{customer}', 'CustomerController@update')->name('patch_customer')->middleware('perm:customers_edit');
        Route::get('/remove/{customer}', 'CustomerController@destroy')->name('delete_customer')->middleware('perm:customers_delete');
        Route::get('/search', 'CustomerController@search_customer')->name('search_customer');
        Route::get('/search/contacts', 'CustomerController@search_customer_contact')->name('search_customer_contact');

        Route::post('/contacts', 'CustomerController@all_contacts')->name('datatables_customer_contacts_all');
        Route::get('/contacts', 'CustomerController@contacts_show')->name('customer_contacts');

         Route::get('/profile/{customer}?group=contacts&id={contact_id}', 'CustomerController@profile')->name('edit_customer_contact');

        Route::post('/contacts/fetch/emails/{customer}', 'CustomerController@contact_emails_by_customer_id')->name('get_contact_emails_by_customer_id');

        Route::post('/get/contacts', 'CustomerController@contacts_paginate')->name('datatables_customer_contacts');
        Route::post('/contacts/{customer}/add', 'CustomerController@add_contact')->name('add_customer_contact');
        Route::post('/contacts/{customer}/update', 'CustomerController@update_contact')->name('update_customer_contact');
        Route::get('/contacts/remove/{contact}', 'CustomerController@destroy_contact')->name('delete_customer_contact');
        Route::get('/contacts/?id={contacts}', 'CustomerController@all_contacts')->name('show_customer_contact');


        Route::post('/change/status', 'CustomerController@change_customer_status')->name('change_customer_status');

        Route::post('/contacts/details', 'CustomerController@get_contact_details')->name('get_customer_contact_details_for_displaying');
        Route::post('/contacts/edit', 'CustomerController@edit_contact_details')->name('get_customer_contact_details');
        Route::post('/contacts/change/status', 'CustomerController@change_contact_status')->name('change_customer_contact_status');
        Route::post('/contacts/change/primary', 'CustomerController@change_primary_contact')->name('change_customer_primary_contact');


        Route::get('/import', 'CustomerController@import_page')->name('import_customer_page')->middleware('perm:customers_create');
        Route::post('/import', 'CustomerController@import')->name('import_customer')->middleware('perm:customers_create');

        Route::get('/import/download/sample', 'CustomerController@download_sample_customer_import_file')->name('download_sample_customer_import_file');


        Route::post('/report', 'CustomerController@report_paginate')->name('customer_report');

    });



    // Vendors

    Route::prefix('vendors')->group(function (){

        Route::get('/', 'VendorController@index')->name('vendors_list');

        Route::post('/paginated/list', 'VendorController@paginate')->name('datatables_vendors')->middleware('perm:vendors_view');

        Route::get('/create', 'VendorController@create')->name('add_vendor_page')->middleware('perm:vendors_create');
        Route::post('/save', 'VendorController@store')->name('post_vendor')->middleware('perm:vendors_create');

        Route::get('/edit/{vendor}', 'VendorController@edit')->name('edit_vendor_page')->middleware('perm:vendors_edit');

        Route::get('/profile/{vendor}', 'VendorController@profile')->name('view_vendor_page')->middleware('perm:vendors_view');

        Route::patch('/save/{vendor}', 'VendorController@update')->name('patch_vendor')->middleware('perm:vendors_edit');
        Route::get('/remove/{vendor}', 'VendorController@destroy')->name('delete_vendor')->middleware('perm:vendors_delete');
 

        Route::post('/change/status', 'VendorController@change_vendor_status')->name('change_vendor_status');

    });


    // Projects

    Route::prefix('projects')->group(function (){

        Route::get('/', 'ProjectsController@index')->name('projects_list');
        Route::post('/paginated/list', 'ProjectsController@paginate')->name('datatables_projects');
        Route::get('/create', 'ProjectsController@create')->name('add_projects')->middleware('perm:projects_create');
        Route::post('/save', 'ProjectsController@store')->name('post_project')->middleware('perm:projects_create');

        Route::get('/edit/{project}', 'ProjectsController@edit')->name('edit_project_page')->middleware('perm:projects_edit');
        Route::patch('/save/{project}', 'ProjectsController@update')->name('patch_project')->middleware('perm:projects_edit');

        Route::get('/remove/{project}', 'ProjectsController@destroy')->name('delete_project')->middleware('perm:projects_delete');
        Route::get('/view/{project}', 'ProjectsController@show')->name('show_project_page');
        


        Route::get('/milestones', 'ProjectsController@get_milestones_by_project_id')->name('get_milestones_by_project_id');
        Route::post('/milestones/{project}', 'MilestoneController@paginate')->name('get_project_milestones');
        Route::post('/change/status/{project}', 'ProjectsController@change_status')->name('change_project_status');


        Route::post('/attachment/create/{project}', 'ProjectsController@add_attachment')->name('project_add_attachment');
        Route::post('/attachments/get/{project}', 'ProjectsController@get_attachments')->name('project_attachment_datatable');


        Route::get('/details/by/customer', 'ProjectsController@get_project_by_customer_id')->name('get_project_by_customer_id');
        Route::get('/details/by/customer/contact', 'ProjectsController@get_project_by_customer_contact_id')->name('get_project_by_customer_contact_id');



        // Milestone
        
        Route::post('/milestone/add', 'MilestoneController@store')->name('add_project_milestone');
        Route::post('/milestone/edit', 'MilestoneController@edit')->name('get_milestone_information');
        Route::post('/milestone/save', 'MilestoneController@update')->name('update_project_milestone');
        Route::get('/project/milestone/{milestone}', 'MilestoneController@destroy')->name('delete_project_milestone');


        Route::get('/invoice/modal/{project}', 'ProjectsController@invoice_project_modal_content')->name('get_invoice_project_modal_content');

    });

    // TimeSheet
     Route::prefix('timesheets')->group(function (){
       
        Route::post('/list', 'TimeSheetController@paginate')->name('datatables_timesheet');
        Route::post('/create', 'TimeSheetController@store')->name('add_time_sheet');
        Route::post('/edit', 'TimeSheetController@edit')->name('get_time_sheet_information');
        Route::post('/save', 'TimeSheetController@update')->name('update_time_sheet');
        Route::get('/remove/{time_sheet}', 'TimeSheetController@destroy')->name('delete_time_sheet');
        Route::post('/report', 'TimeSheetController@report_paginate')->name('datatables_timesheet_report');
    });

    

    //Item
     Route::prefix('items')->group(function (){
        Route::get('/', 'ItemController@index')->name('item_list');
        Route::post('/list', 'ItemController@paginate')->name('datatables_items')->middleware('perm:items_view');
        Route::get('/create', 'ItemController@create')->name('add_item')->middleware('perm:items_create');
        Route::post('/create', 'ItemController@store')->name('post_item')->middleware('perm:items_create');
        Route::get('/edit/{item}', 'ItemController@edit')->name('edit_item_page')->middleware('perm:items_edit');
        Route::patch('/edit/{item}', 'ItemController@update')->name('patch_item')->middleware('perm:items_edit');
        Route::get('/remove/{item}', 'ItemController@destroy')->name('delete_item_page')->middleware('perm:items_delete');
    });


   
    // Notes
    Route::prefix('notes')->group(function (){

        Route::post('/update', 'NoteController@update')->name('patch_note');       
        Route::post('/destroy', 'NoteController@destroy')->name('delete_note');


    });

   



    // Leads    

    Route::prefix('leads')->group(function (){


        Route::get('/', 'LeadController@index')->name('leads_list')->middleware('perm:[leads_view|leads_view_own|leads_create|leads_edit],is_multiple');
        Route::post('/list', 'LeadController@paginate')->name('datatables_leads')->middleware('perm:[leads_view|leads_view_own],is_multiple');

        Route::get('/create', 'LeadController@create')->name('add_lead_page')->middleware('perm:leads_create');
        Route::post('/create', 'LeadController@store')->name('post_lead')->middleware('perm:leads_create');
       
        Route::get('/show/{lead}', 'LeadController@show')->name('show_lead_page')->middleware('perm:[leads_view|leads_view_own],is_multiple');

        Route::get('/edit/{lead}', 'LeadController@edit')->name('edit_lead_page')->middleware('perm:leads_edit');
        Route::patch('/edit/{lead}', 'LeadController@update')->name('patch_lead')->middleware('perm:leads_edit');

        Route::get('/remove/{lead}', 'LeadController@destroy')->name('delete_lead')->middleware('perm:leads_delete');

        Route::get('/mark/junk/{lead}', 'LeadController@mark_as_junk')->name('mark_as_junk')->middleware('perm:leads_edit');
        Route::get('/mark/lost/{lead}', 'LeadController@mark_as_lost')->name('mark_as_lost')->middleware('perm:leads_edit');
        Route::post('/mark/important/{lead}', 'LeadController@mark_as_important')->name('mark_as_important')->middleware('perm:leads_edit');;



        Route::get('/import', 'LeadController@import_page')->name('import_lead_page')->middleware('perm:leads_create');
        Route::post('/import', 'LeadController@import')->name('import_lead')->middleware('perm:leads_create');

        Route::get('/import/download/sample', 'LeadController@download_sample_lead_import_file')->name('download_sample_lead_import_file');

   
        
        Route::post('/note/create/{lead}', 'LeadController@add_note')->name('lead_add_note')->middleware('perm:[leads_view|leads_view_own],is_multiple');

        Route::post('/log/touch/{lead}', 'LeadController@log_touch')->name('post_log_touch');

        Route::post('/save/social-link/{lead}', 'LeadController@save_social_link')->name('post_social_link');
        Route::post('/remove/social-link/{lead}', 'LeadController@remove_social_link')->name('remove_social_link');

        Route::post('/save/smart-summary/{lead}', 'LeadController@save_smart_summary')->name('post_smart_summary');
        Route::post('/remove/smart-summary/{lead}', 'LeadController@remove_smart_summary')->name('remove_smart_summary');

        Route::post('/report/conversion/by/month', 'LeadController@get_report_conversion_by_month_for_graph')->name('get_report_conversion_by_month_for_graph');

    });

    // Reminders
    Route::prefix('reminders')->group(function (){

        Route::get('/', 'ReminderController@index')->name('reminder_list');
        Route::post('/list', 'ReminderController@paginate')->name('datatables_reminders');
        Route::post('/create', 'ReminderController@store')->name('post_reminder');

         Route::post('/information/', 'ReminderController@edit')->name('get_reminder_information');
        Route::post('/edit/{reminder?}', 'ReminderController@update')->name('patch_reminder');
        Route::get('/remove/{reminder}', 'ReminderController@destroy')->name('delete_reminder');

    });
       

    // Task
    Route::prefix('tasks')->group(function (){

        Route::get('/', 'TaskController@index')->name('task_list');
        Route::get('/kanban', 'TaskController@kanban_view')->name('task_canban_view');

        Route::post('/upload/attachment', 'TaskController@upload_attachment')->name('upload_task_attachment');
        
        Route::post('/list', 'TaskController@paginate')->name('datatables_tasks');
        Route::get('/create', 'TaskController@create')->name('add_task_page')->middleware('perm:tasks_create');
        Route::post('/create', 'TaskController@store')->name('post_task')->middleware('perm:tasks_create');

        Route::get('/edit/{task}', 'TaskController@edit')->name('edit_task_page')->middleware('perm:tasks_edit');
        Route::patch('/edit/{task?}', 'TaskController@update')->name('patch_task')->middleware('perm:tasks_edit');
        Route::get('/remove/{task}', 'TaskController@destroy')->name('delete_task')->middleware('perm:tasks_delete');

        Route::get('/show/{task}', 'TaskController@show')->name('show_task_page');
        Route::post('/comment/{task}', 'TaskController@post_task_comment')->name('post_task_comment');
        Route::patch('/comment/{task}/{comment}', 'TaskController@update_task_comment')->name('patch_task_comment');

        Route::get('/show/{task}/change/status/{status_id}', 'TaskController@change_status')->name('task_change_status');
        Route::post('/change/status', 'TaskController@change_status')->name('task_change_status_ajax');


        Route::get('/related', 'TaskController@task_related')->name('task_related');
        Route::get('/parent/list', 'TaskController@parent_tasks')->name('get_parent_tasks');


        Route::get('/component', 'TaskController@tasks_by_component_id')->name('tasks_by_component_id');

        Route::post('/component/{component}/{id}', 'TaskController@tasks_by_component_id_paginate')->name('datatable_tasks_by_component_id');

        Route::post('/comments/{task}', 'TaskController@comments')->name('datatable_tasks_comments');

        Route::get('/comment/delete/{comment}', 'CommentController@destroy')->name('delete_comment');

        Route::post('/update/milestone', 'TaskController@update_task_milestone')->name('task_update_milestone');

        Route::post('/assign/{task}', 'TaskController@assign_task')->name('assign_task');

        Route::get('/show/{task}?comment={comment_id}', 'TaskController@show')->name('show_task_comment');


        Route::get('/convert/ticket/{ticket_comment_thread_id}', 'TaskController@convert_ticket_to_task')->name('convert_ticket_to_task');
   
        
    });

    // Proposal
    Route::prefix('proposals')->group(function (){

        Route::get('/', 'ProposalController@index')->name('proposal_list');
        Route::post('/list', 'ProposalController@paginate')->name('datatables_proposal')->middleware('perm:[proposals_view|proposals_view_own],is_multiple');

        Route::get('/create', 'ProposalController@create')->name('add_proposal_page')->middleware('perm:proposals_create');
        Route::post('/create', 'ProposalController@store')->name('post_proposal')->middleware('perm:proposals_create');

        Route::get('/edit/{proposal?}', 'ProposalController@edit')->name('edit_proposal_page')->middleware('perm:proposals_edit');
        Route::patch('/edit/{proposal}', 'ProposalController@update')->name('patch_proposal')->middleware('perm:proposals_edit');
        Route::get('/remove/{proposal?}', 'ProposalController@destroy')->name('delete_proposal')->middleware('perm:proposals_delete');

        Route::get('/?id={proposal}', 'ProposalController@index')->name('show_proposal_page')->middleware('perm:proposals_view');


        Route::get('/related', 'ProposalController@related_component')->name('related_component');
        Route::get('/products/list', 'ProposalController@search_product')->name('proposal_search_product');
        Route::get('/details', 'ProposalController@get_proposal_details_ajax')->name('get_proposal_details_ajax');
        Route::get('/items', 'ProposalController@get_proposal_items_ajax')->name('get_proposal_items_ajax');
        
        Route::post('/save/content', 'ProposalController@save_proposal_content')->name('save_proposal_content');

        Route::post('/status/change', 'ProposalController@change_status')->name('ajax_change_proposal_status');

        Route::post('/send/email', 'ProposalController@send_to_email')->name('proposal_send_to_email');


    });

    

    

    // Estimate
    Route::prefix('estimates')->group(function (){

    Route::get('/', 'EstimateController@index')->name('estimate_list');
    Route::post('/list', 'EstimateController@paginate')->name('datatables_estimate')->middleware('perm:[estimates_view|estimates_view_own],is_multiple');
    Route::get('/create', 'EstimateController@create')->name('add_estimate_page')->middleware('perm:estimates_create');
    Route::post('/create', 'EstimateController@store')->name('post_estimate')->middleware('perm:estimates_create');
    Route::get('/edit/{estimate?}', 'EstimateController@edit')->name('edit_estimate_page')->middleware('perm:estimates_edit');
    Route::patch('/edit/{estimate}', 'EstimateController@update')->name('patch_estimate')->middleware('perm:estimates_edit');
    Route::get('/remove/{estimate?}', 'EstimateController@destroy')->name('delete_estimate')->middleware('perm:estimates_delete');

    Route::get('/details', 'EstimateController@get_estimate_details_ajax')->name('get_estimate_details_ajax');
    Route::post('/status/change', 'EstimateController@change_status')->name('ajax_change_estimate_status');

    Route::post('/send/email', 'EstimateController@send_to_email')->name('estimate_send_to_email');

    Route::get('/?id={estimate}', 'EstimateController@index')->name('show_estimate_page');

    Route::get('/convert/proposal/{proposal_id?}', 'EstimateController@convert_to_estimate_from_proposal')->name('convert_to_estimate_from_proposal')->middleware('perm:estimates_create');

    });



    // Invoice
    Route::prefix('invoices')->group(function (){


    Route::get('/', 'InvoiceController@index')->name('invoice_list');
    Route::post('/list', 'InvoiceController@paginate')->name('datatables_invoice')->middleware('perm:[invoices_view|invoices_view_own],is_multiple');
    Route::get('/create', 'InvoiceController@create')->name('add_invoice_page')->middleware('perm:invoices_create');
    Route::post('/create', 'InvoiceController@store')->name('post_invoice')->middleware('perm:invoices_create');
    Route::get('/edit/{invoice?}', 'InvoiceController@edit')->name('edit_invoice_page')->middleware('perm:invoices_edit');
    Route::patch('/edit/{invoice}', 'InvoiceController@update')->name('patch_invoice')->middleware('perm:invoices_edit');
    Route::get('/remove/{invoice?}', 'InvoiceController@destroy')->name('delete_invoice')->middleware('perm:invoices_delete');

    Route::get('/details', 'InvoiceController@get_invoice_details_ajax')->name('get_invoice_details_ajax');
    Route::post('/status/change', 'InvoiceController@change_status')->name('ajax_change_invoice_status');

    

    Route::get('?id={invoice}', 'InvoiceController@index')->name('show_invoice_page')->middleware('perm:invoices_view');
        
    Route::post('/send/email', 'InvoiceController@send_to_email')->name('invoice_send_to_email');

    Route::post('/payment/receive', 'InvoiceController@receive_payment')->name('receive_payment');
    Route::get('?id={invoice?}', 'InvoiceController@index')->name('invoice_link');

    Route::get('/get/payment/', 'InvoiceController@get_invoice_payments')->name('get_invoice_payments');

    Route::get('/convert/proposal/{proposal_id?}', 'InvoiceController@convert_to_invoice_from_proposal')->name('convert_to_invoice_from_proposal')->middleware('perm:invoices_create');
    Route::get('/convert/estimate/{estimate_id?}', 'InvoiceController@convert_to_invoice_from_estimate')->name('convert_to_invoice_from_estimate')->middleware('perm:invoices_create');

    Route::get('/convert/expense/{expense_id?}', 'InvoiceController@convert_to_invoice_from_expense')->name('convert_to_invoice_from_expense')->middleware('perm:invoices_create');

    Route::post('/customer/unbilled/tasks', 'InvoiceController@get_unbilled_timesheets_and_expenses_by_customer_id')->name('get_unbilled_tasks_by_customer_id');

    Route::post('/report', 'InvoiceController@report_paginate')->name('report_invoice');
    Route::post('/item/report', 'InvoiceController@report_item_paginate')->name('report_item');

    Route::post('/update/recurring/details', 'InvoiceController@update_recurring_invoice_setting')->name('update_recurring_invoice_setting');


    Route::post('/create/for/project/{project}', 'InvoiceController@create_invoice_for_a_project')->name('create_invoice_for_a_project');

    Route::post('/children', 'InvoiceController@get_child_invoices')->name('get_child_invoices');

    Route::get('/recurring', 'InvoiceController@recurring_invoices')->name('recurring_invoices_list');
    Route::post('/recurring', 'InvoiceController@paginate_recurring_invoices')->name('datatable_recurring_invoices');
   
    
    });



    // Credit Note
    Route::prefix('credit-notes')->group(function (){

    Route::get('/', 'CreditNoteController@index')->name('credit_note_list');
    Route::post('/list', 'CreditNoteController@paginate')->name('datatables_credit_note')->middleware('perm:[credit_notes_view|credit_notes_view_own],is_multiple');
    Route::get('/create', 'CreditNoteController@create')->name('add_credit_note_page')->middleware('perm:credit_notes_create');
    Route::post('/create', 'CreditNoteController@store')->name('post_credit_note')->middleware('perm:credit_notes_create');
    Route::get('/edit/{credit_note?}', 'CreditNoteController@edit')->name('edit_credit_note_page')->middleware('perm:credit_notes_edit');
    Route::patch('/edit/{credit_note}', 'CreditNoteController@update')->name('patch_credit_note')->middleware('perm:credit_notes_edit');
    Route::get('/remove/{credit_note?}', 'CreditNoteController@destroy')->name('delete_credit_note')->middleware('perm:credit_notes_delete');

    Route::get('/details', 'CreditNoteController@get_credit_note_details_ajax')->name('get_credit_note_details_ajax');
    Route::post('/status/change', 'CreditNoteController@change_status')->name('ajax_change_credit_note_status');

    Route::post('/send/email', 'CreditNoteController@send_to_email')->name('credit_note_send_to_email');

    Route::get('/?id={credit_note}', 'CreditNoteController@index')->name('show_credit_note_page');


    
    Route::post('/by/customer', 'CreditNoteController@get_available_credit_notes_by_customer_id')->name('get_available_credit_notes_by_customer_id');

Route::post('/apply', 'CreditNoteController@apply_credit_to_invoice')->name('apply_credit_to_invoice');

    Route::post('/invoices', 'CreditNoteController@get_invoices_applied_to')->name('credit_note_get_invoices');
    
    });



    //Payment

    Route::prefix('payments')->group(function (){

    Route::get('/', 'PaymentController@index')->name('payment_list');
    Route::post('/list', 'PaymentController@paginate')->name('datatables_payment')->middleware('perm:[payments_view|invoices_view|invoices_view_own],is_multiple');

    Route::get('/create', 'PaymentController@create')->name('add_payment')->middleware('perm:payments_create');
    Route::post('/create', 'PaymentController@store')->name('post_payment')->middleware('perm:payments_create');
    Route::get('/edit/{payment}', 'PaymentController@edit')->name('edit_payment_page')->middleware('perm:payments_edit');
    Route::get('/edit', 'PaymentController@edit')->name('edit_payment_page_js_link')->middleware('perm:payments_edit');

    Route::patch('/{payment}', 'PaymentController@update')->name('patch_payment')->middleware('perm:payments_edit');
    Route::get('/remove/{payment}', 'PaymentController@destroy')->name('delete_payment_page')->middleware('perm:payments_delete');

    Route::get('/receipt/download/pdf/{payment}', 'PaymentController@download_receipt_pdf')->name('download_receipt');

    Route::get('/{payment}', 'PaymentController@show')->name('show_payment_page')->middleware('perm:[payments_view|invoices_view_own],is_multiple');

    Route::post('/report', 'PaymentController@report_paginate')->name('report_payment');
    
    });




    // Expenses
    Route::prefix('expenses')->group(function (){
    
        Route::get('/', 'ExpenseController@index')->name('expense_list');
        Route::post('/', 'ExpenseController@paginate')->name('datatables_expense')->middleware('perm:[expenses_view|expenses_view_own],is_multiple');
        Route::get('/create', 'ExpenseController@create')->name('add_expense_page')->middleware('perm:expenses_create');
        Route::post('/create', 'ExpenseController@store')->name('post_expense')->middleware('perm:expenses_create');
        Route::get('/edit/{expense?}', 'ExpenseController@edit')->name('edit_expense_page')->middleware('perm:expenses_edit');
        Route::patch('edit/{expense}', 'ExpenseController@update')->name('patch_expense')->middleware('perm:expenses_edit');
        Route::get('/remove/{expense?}', 'ExpenseController@destroy')->name('delete_expense')->middleware('perm:expenses_delete');

        Route::get('/details', 'ExpenseController@get_expense_details_ajax')->name('get_expense_details_ajax');
        Route::get('/download/receipt/{filename}', 'ExpenseController@download_attachment')->name('download_attachment_expense');


        Route::get('?id={expense}', 'ExpenseController@index')->name('show_expense_page')->middleware('perm:expenses_view');


    });


    // Teams
    Route::prefix('teams')->group(function (){

        Route::get('/', 'TeamController@index')->name('teams_list')->middleware('perm:teams_view');
        Route::post('', 'TeamController@paginate')->name('datatables_teams')->middleware('perm:teams_view');
        Route::post('create', 'TeamController@store')->name('post_team')->middleware('perm:teams_create');
        Route::post('edit', 'TeamController@edit')->name('get_information_team')->middleware('perm:teams_edit');
        Route::post('update', 'TeamController@update')->name('patch_team')->middleware('perm:teams_edit');
        Route::get('remove/{team}', 'TeamController@destroy')->name('delete_team')->middleware('perm:teams_delete');

        Route::get('/members', 'UserController@index')->name('team_members_list');
        Route::post('/members', 'UserController@paginate')->name('datatables_team_members');

        Route::get('/members/create', 'UserController@create')->name('add_team_member_page')->middleware('perm:team_members_create');
        Route::post('/members/create', 'UserController@store')->name('post_team_member')->middleware('perm:team_members_create');

        Route::get('/members/edit/{member}', 'UserController@edit')->name('edit_team_member_page')->middleware('perm:team_members_edit');
        Route::patch('/members/edit/{member}', 'UserController@update')->name('patch_team_member')->middleware('perm:team_members_edit');

        Route::post('/members/remove', 'UserController@destroy')->name('delete_team_member')->middleware('perm:team_members_delete');

        Route::get('/members/suggestion/list', 'UserController@get_members_for_suggestion_list')->name('get_members_for_suggestion_list');

        Route::get('/members/profile/{member}', 'UserController@profile')->name('member_profile');
        Route::post('/members/change/photo/{member}', 'UserController@change_photo')->name('team_member_change_photo');

        Route::get('/members/profile/{member}?group=notifications', 'UserController@notifications')->name('member_view_all_notifications');
        Route::post('/members/notifications', 'UserController@notification_paginate')->name('datatable_member_notifications');

        Route::get('/notifications/redirect/{id}', 'UserController@notification_redirect_url')->name('notification_redirect_url');
        Route::get('/notifications/mark/read/all', 'UserController@mark_all_notification_as_read')->name('notification_all_mark_as_read');

        Route::get('/members/search', 'UserController@search_team_member')->name('search_team_member');


        // Team member skills
        Route::get('/skills', 'SkillController@index')->name('skills_list');     
        Route::post('/skills/create', 'SkillController@store')->name('post_skills');
        Route::post('/skills/list', 'SkillController@paginate')->name('datatables_skills');
        Route::patch('/skills/{skill}', 'SkillController@update')->name('patch_skills');
        Route::get('/skills/remove/{skill}', 'SkillController@destroy')->name('delete_skills');
        

    });


     //Ticket
    Route::group(['prefix' => 'tickets', 'middleware' => ['feature_enabled:support'] ] , function (){     
   

        Route::get('/', 'TicketController@index')->name('ticket_list')->middleware('perm:[tickets_view|tickets_view_own|tickets_create|tickets_edit],is_multiple');
        Route::post('/list', 'TicketController@paginate')->name('datatables_tickets');
        Route::get('/create', 'TicketController@create')->name('add_ticket_page')->middleware('perm:tickets_create');
        Route::post('/create', 'TicketController@store')->name('post_ticket')->middleware('perm:tickets_create');
        Route::get('/view/{ticket}?group=settings', 'TicketController@show')->name('edit_ticket_page')->middleware('perm:[tickets_view|tickets_view_own|tickets_edit],is_multiple');
        Route::patch('/edit/{ticket}', 'TicketController@update')->name('patch_ticket')->middleware('perm:tickets_edit');
        Route::get('/remove/{ticket}', 'TicketController@destroy')->name('delete_ticket')->middleware('perm:tickets_delete');

        Route::get('/view/{ticket}', 'TicketController@show')->name('show_ticket_page');
        Route::post('/predefined-reply', 'TicketController@get_predefined_reply')->name('get_ticket_predefined_reply');
        Route::post('/reply/{ticket}', 'TicketController@add_reply')->name('ticket_add_reply');
        Route::post('/change/status', 'TicketController@change_status')->name('ticket_change_status');
        Route::post('/note/create/{ticket}', 'TicketController@add_note')->name('ticket_add_note'); 
    
    });

 


    Route::prefix('manage/knowledge-base')->group(function (){



        Route::get('/', 'ArticleController@index')->name('knowledge_base_article_list');

        Route::post('/list', 'ArticleController@paginate')->name('datatables_knowledge_base_article');
        Route::get('/create', 'ArticleController@create')->name('add_knowledge_base_article_page')->middleware('perm:Knowledge_base_create');
        Route::post('/create', 'ArticleController@store')->name('post_knowledge_base_article')->middleware('perm:Knowledge_base_create');
        
        Route::get('/edit/{article}', 'ArticleController@edit')->name('edit_knowledge_base_article_page')->middleware('perm:Knowledge_base_edit');
       
        Route::patch('/edit/{article}', 'ArticleController@update')->name('patch_knowledge_base_article')->middleware('perm:Knowledge_base_edit');
        Route::get('/remove/{article}', 'ArticleController@destroy')->name('delete_knowledge_base_article')->middleware('perm:Knowledge_base_delete');

       
         // Knowelge Base Article Groups        
        Route::group(['prefix' => 'groups', 'middleware' => ['perm:[Knowledge_base_create|Knowledge_base_edit],is_multiple'] ] , function (){ 

            Route::get('/', 'ArticleGroupController@index')->name('knowledge_base_article_group_list');
            Route::post('/list', 'ArticleGroupController@paginate')->name('datatables_knowledge_base_article_group');
            Route::post('/create', 'ArticleGroupController@store')->name('post_knowledge_base_article_group');
            Route::post('/edit', 'ArticleGroupController@edit')->name('get_information_knowledge_base_article_group');
            Route::post('/update', 'ArticleGroupController@update')->name('patch_knowledge_base_article_group');
            Route::get('/remove/{group}', 'ArticleGroupController@destroy')->name('delete_knowledge_base_article_group');
        });

        
    });



    // Settings
    Route::prefix('settings')->group(function (){     


        Route::get('/', 'SettingsController@general_information')->name('settings_main_page');
        Route::patch('/', 'SettingsController@update_general_information')->name('patch_company_information');
        Route::get('/email', 'SettingsController@email')->name('settings_email_page');
        Route::patch('/email', 'SettingsController@update_email')->name('patch_settings_email');
        Route::post('/email/test', 'SettingsController@send_test_email')->name('send_test_email');

        Route::get('/email/templates', 'SettingsController@email_template_home')->name('settings_email_template_home_page');

        Route::get('/email/templates/{template_name}', 'SettingsController@email_template_page')->name('settings_email_template_page');
        Route::patch('/email/templates', 'SettingsController@update_email_template')->name('patch_settings_email_template');

        Route::get('/pusher', 'SettingsController@pusher_page')->name('settings_pusher_page');
        Route::patch('/pusher', 'SettingsController@update_pusher')->name('patch_settings_pusher');

        Route::get('/invoice', 'InvoiceController@settings')->name('settings_invoice_page');
        Route::patch('/invoice', 'InvoiceController@update_settings')->name('patch_settings_invoice');

        Route::get('/estimate', 'EstimateController@settings')->name('settings_estimate_page');
        Route::patch('/estimate', 'EstimateController@update_settings')->name('patch_settings_estimate');

        Route::get('/proposal', 'ProposalController@settings')->name('settings_proposal_page');
        Route::patch('/proposal', 'ProposalController@update_settings')->name('patch_settings_proposal');

        // Support Configurations
        Route::get('support/configuration', 'TicketController@configuration_page')->name('support_configuration_page');
        Route::patch('support/configuration', 'TicketController@update_configuration_page')->name('patch_support_configuration');

        // Department
        Route::prefix('support/department')->group(function (){
            Route::get('/', 'DepartmentController@index')->name('department_list');
            Route::post('/list', 'DepartmentController@paginate')->name('datatables_departments');
            Route::post('/create', 'DepartmentController@store')->name('post_department');
            Route::post('/edit', 'DepartmentController@edit')->name('get_information_department');
            Route::post('/update', 'DepartmentController@update')->name('patch_department');
            Route::get('/remove/{department}', 'DepartmentController@destroy')->name('delete_department');
            Route::post('/check/imap', 'DepartmentController@check_imap_connection')->name('check_imap_connection');
            
        });

         // Ticket Services
        Route::prefix('support/tickets/services')->group(function (){
            Route::get('/', 'TicketServiceController@index')->name('ticket_service_list');
            Route::post('/list', 'TicketServiceController@paginate')->name('datatables_ticket_services');
            Route::post('/create', 'TicketServiceController@store')->name('post_ticket_service');
            Route::post('/edit', 'TicketServiceController@edit')->name('get_information_ticket_services');
            Route::post('/update', 'TicketServiceController@update')->name('patch_ticket_service');
            Route::get('/remove/{obj}', 'TicketServiceController@destroy')->name('delete_ticket_service');
        });


         // Ticket Priorities
        Route::prefix('support/tickets/priorities')->group(function (){
            Route::get('/', 'TicketPriorityController@index')->name('ticket_priority_list');
            Route::post('/list', 'TicketPriorityController@paginate')->name('datatables_ticket_priorities');
            Route::post('/create', 'TicketPriorityController@store')->name('post_ticket_priority');
            Route::post('/edit', 'TicketPriorityController@edit')->name('get_information_ticket_priorities');
            Route::post('/update', 'TicketPriorityController@update')->name('patch_ticket_priority');
            Route::get('/remove/{obj}', 'TicketPriorityController@destroy')->name('delete_ticket_priority');
        });

       


         // Ticket Status
        Route::prefix('support/tickets/statuses')->group(function (){
            Route::get('/', 'TicketStatusController@index')->name('ticket_status_list');
            Route::post('/list', 'TicketStatusController@paginate')->name('datatables_ticket_statuses');
            Route::post('/create', 'TicketStatusController@store')->name('post_ticket_status');
            Route::post('/edit', 'TicketStatusController@edit')->name('get_information_ticket_status');
            Route::post('/update', 'TicketStatusController@update')->name('patch_ticket_status');
            Route::get('/remove/{obj}', 'TicketStatusController@destroy')->name('delete_ticket_status');
        });

         // Ticket Pre Defined Replies
        Route::prefix('support/tickets/predefined-replies')->group(function (){
            Route::get('/', 'PreDefinedReplyController@index')->name('ticket_pre_defined_replies_list');
            Route::post('/list', 'PreDefinedReplyController@paginate')->name('datatables_ticket_pre_difined_replies');
            Route::post('/create', 'PreDefinedReplyController@store')->name('post_ticket_pre_difined_reply');
            Route::post('/edit', 'PreDefinedReplyController@edit')->name('get_information_ticket_pre_difined_reply');
            Route::post('/update', 'PreDefinedReplyController@update')->name('patch_ticket_pre_difined_reply');
            Route::get('/remove/{obj}', 'PreDefinedReplyController@destroy')->name('delete_ticket_pre_difined_reply');
        });

        

        
        // Tags
        Route::prefix('tags')->group(function (){ 

            Route::get('/', 'TagController@index')->name('tags_list');
            Route::post('/list', 'TagController@paginate')->name('datatables_tags');
            Route::get('/create', 'TagController@create')->name('add_tag_page');
            Route::post('/save', 'TagController@store')->name('post_tag');
            Route::post('/get/information/{tag}', 'TagController@edit')->name('get_information_tag');
            Route::patch('/edit/{tag}', 'TagController@update')->name('patch_tag');
            Route::get('/remove/{tag}', 'TagController@destroy')->name('delete_tag');

        });

         // Lead Status
        Route::prefix('leads')->group(function (){ 
       
        Route::get('/status', 'LeadStatusController@index')->name('leads_statuses_list');
        Route::post('/status/create', 'LeadStatusController@store')->name('post_lead_status');
        Route::post('/status/list', 'LeadStatusController@paginate')->name('datatables_leads_status');
        Route::patch('/status/{status}', 'LeadStatusController@update')->name('patch_lead_status');
        Route::get('/status/remove/{status}', 'LeadStatusController@destroy')->name('delete_leads_status');


        // Lead Sources
        Route::get('/sources', 'LeadSourceController@index')->name('lead_sources_list');     
        Route::post('/lsource/create', 'LeadSourceController@store')->name('post_lead_source');
        Route::post('/sources/list', 'LeadSourceController@paginate')->name('datatables_leads_source');
        Route::patch('/source/{source}', 'LeadSourceController@update')->name('patch_lead_source');
        Route::get('/source/remove/{source}', 'LeadSourceController@destroy')->name('delete_leads_source');

        });


        // Customer
        Route::prefix('customer')->group(function (){

            // Groups
            Route::get('/groups', 'CustomerGroupController@index')->name('customer_groups_list');
            Route::post('/groups', 'CustomerGroupController@paginate')->name('datatables_customer_groups');
            Route::post('/groups/add', 'CustomerGroupController@store')->name('post_customer_group');
            Route::post('/groups/edit', 'CustomerGroupController@edit')->name('get_information_customer_group');
            Route::post('/groups/update', 'CustomerGroupController@update')->name('patch_customer_group');
            Route::get('/groups/remove/{group}', 'CustomerGroupController@destroy')->name('delete_customer_group');

            // Support Configurations
        Route::get('configuration', 'CustomerController@configuration_page')->name('customer_configuration_page');
        Route::patch('configuration', 'CustomerController@update_configuration_page')->name('customer_support_configuration');

        });


        // Finance
        Route::prefix('finance')->group(function (){


            // Taxes
            Route::prefix('taxes')->group(function (){
                Route::get('/', 'TaxController@index')->name('tax_list');
                Route::post('/list', 'TaxController@paginate')->name('datatables_taxes');
                Route::post('/add', 'TaxController@store')->name('post_tax');
                Route::post('/edit', 'TaxController@edit')->name('get_information_tax');
                Route::post('/update', 'TaxController@update')->name('patch_tax');
                Route::get('/remove/{obj}', 'TaxController@destroy')->name('delete_tax');
            });


           



            // Expense Category
            Route::get('expense/categories', 'ExpenseCategoryController@index')->name('expense_categories_list');
            Route::post('expense/categories', 'ExpenseCategoryController@paginate')->name('datatables_expense_categories');
            Route::post('expense/categories/add', 'ExpenseCategoryController@store')->name('post_expense_category');
            Route::post('expense/categories/edit', 'ExpenseCategoryController@edit')->name('get_information_expense_category');
            Route::post('expense/categories/update', 'ExpenseCategoryController@update')->name('patch_expense_category');
            Route::get('expense/categories/remove/{obj}', 'ExpenseCategoryController@destroy')->name('delete_expense_category');



            Route::prefix('payment/modes')->group(function (){

                Route::get('offline', 'PaymentModeController@offline_modes_index')->name('payment_modes_list');
                Route::post('offline', 'PaymentModeController@offline_modes_paginate')->name('datatables_payment_modes');

                Route::post('offline/create', 'PaymentModeController@offline_mode_store')->name('post_payment_mode');
                Route::post('offline/get', 'PaymentModeController@offline_mode_edit')->name('get_information_payment_mode');
                Route::post('offline/update', 'PaymentModeController@offline_mode_update')->name('patch_payment_mode');
                 Route::get('offline/{mode}', 'PaymentModeController@offline_mode_destroy')->name('delete_payment_mode');

                 Route::post('offline/change/status', 'PaymentModeController@offline_change_mode_status')->name('change_mode_status');

         
                Route::get('online', 'PaymentModeController@online_modes_main')->name('payment_modes_online_page');
                Route::post('online', 'PaymentModeController@store_online_payment_mode')->name('post_payment_modes_online');
              

            });



            Route::get('currencies', 'CurrencyController@index')->name('currency_list');
            Route::post('currencies', 'CurrencyController@paginate')->name('datatables_currencies');
            Route::post('currencies/create', 'CurrencyController@store')->name('post_currency');
            Route::post('currencies/get', 'CurrencyController@edit')->name('get_information_currency');
            Route::post('currencies/update', 'CurrencyController@update')->name('patch_currency');
            Route::get('/currencies/{currency}', 'CurrencyController@destroy')->name('delete_currency');

             Route::post('/currencies/set/default', 'CurrencyController@change_default_currency')->name('change_default_currency');




        });


        // Team Members
        Route::prefix('team')->group(function (){

            // Roles
            Route::get('/user/roles', 'RoleController@index')->name('role_list');
            Route::post('/user/roles', 'RoleController@paginate')->name('datatables_roles');
            Route::get('/user/roles/add', 'RoleController@create')->name('create_role_page');
            Route::post('/user/roles/add', 'RoleController@store')->name('post_role');
            Route::get('/user/roles/edit/{role}', 'RoleController@edit')->name('edit_role_page');
            Route::patch('/user/roles/update/{id}', 'RoleController@update')->name('patch_role');
            Route::get('/user/roles/remove/{role}', 'RoleController@destroy')->name('delete_role');



        });








    });



    Route::prefix('todo')->group(function (){

        Route::post('/list', 'ToDoController@get_all')->name('get_todo_list');
        Route::post('/store', 'ToDoController@store')->name('post_todo_item');
      
        Route::post('/destroy/specific/{todo}', 'ToDoController@destory')->name('delete_todo_item');
        Route::post('/change/status', 'ToDoController@change_status')->name('todo_item_change_status');
        Route::post('/destroy/completed', 'ToDoController@destory_all_completed')->name('todo_destory_all_completed');
        
    });





    // Reports
    
    Route::group(['prefix' => 'reports',  'middleware' => 'perm:reports_view'], function() {
       
        Route::get('/sales', 'ReportController@sales')->name('report_sales_page');
        Route::get('/expenses', 'ReportController@expenses')->name('report_expenses_page');
        Route::get('/expenses/download', 'ReportController@download_expense_report')->name('report_expenses_download');
        Route::get('/activity-log', 'ReportController@activity_log')->name('report_activity_log');
        Route::post('/activity-log', 'ReportController@activity_log_paginate')->name('datatable_activity_log');


        Route::get('/timesheet', 'TimeSheetController@report_page')->name('report_timesheet_page');
        Route::get('/leads', 'LeadController@report_page')->name('lead_report_page');

    });






});


}); // End of Set Global Config Middleware